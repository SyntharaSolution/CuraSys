import { openDB } from 'idb';
import axios from 'axios';

const DB_NAME = 'curasys-db';
const DB_VERSION = 1;

export const initDB = async () => {
    return openDB(DB_NAME, DB_VERSION, {
        upgrade(db) {
            // Create a store for medicines search cache
            if (!db.objectStoreNames.contains('medicines_cache')) {
                db.createObjectStore('medicines_cache', { keyPath: 'uuid' });
            }
            // Create a store for pending sales
            if (!db.objectStoreNames.contains('pending_sales')) {
                db.createObjectStore('pending_sales', { keyPath: 'id', autoIncrement: true });
            }
            // Create a store for held bills
            if (!db.objectStoreNames.contains('held_bills')) {
                db.createObjectStore('held_bills', { keyPath: 'id' });
            }
        }
    });
};

// Caches a list of products into IndexedDB
export const cacheProducts = async (products) => {
    const db = await initDB();
    const tx = db.transaction('medicines_cache', 'readwrite');
    // Clear old cache before setting new
    await tx.objectStore('medicines_cache').clear();
    for (const product of products) {
        await tx.store.put(product);
    }
    await tx.done;
};

// Searches the local cache
export const searchProductsOffline = async (query) => {
    const db = await initDB();
    const allProducts = await db.getAll('medicines_cache');
    
    if (!query) return allProducts;

    const lowerQuery = query.toLowerCase();
    return allProducts.filter(product => {
        return (product.name && product.name.toLowerCase().includes(lowerQuery)) ||
               (product.generic_name && product.generic_name.toLowerCase().includes(lowerQuery)) ||
               (product.sku && product.sku.toLowerCase().includes(lowerQuery));
    });
};

// Saves a checkout payload to the pending sales store
export const savePendingSale = async (salePayload) => {
    const db = await initDB();
    await db.put('pending_sales', {
        payload: salePayload,
        timestamp: new Date().toISOString()
    });
};

// Retrieves all pending sales
export const getPendingSales = async () => {
    const db = await initDB();
    return await db.getAll('pending_sales');
};

// Deletes a specific pending sale
export const deletePendingSale = async (id) => {
    const db = await initDB();
    await db.delete('pending_sales', id);
};

// Uploads all pending sales to the server
export const syncPendingSales = async () => {
    if (!navigator.onLine) return { success: false, count: 0, message: 'Still offline' };

    const pending = await getPendingSales();
    if (pending.length === 0) return { success: true, count: 0, message: 'Nothing to sync' };

    let successCount = 0;
    
    for (const sale of pending) {
        try {
            await axios.post('/api/v1/pharmacy/pos/checkout', sale.payload);
            // On success, remove from IndexedDB
            await deletePendingSale(sale.id);
            successCount++;
        } catch (e) {
            console.error("Failed to sync sale", sale, e);
            // If it failed due to a validation error, we might want to discard it,
            // but usually we keep it and let the user fix it or investigate later.
        }
    }

    return { 
        success: true, 
        count: successCount, 
        message: `Synced ${successCount} out of ${pending.length} pending sales` 
    };
};

// Hold Bill Logic
export const saveHeldBill = async (bill) => {
    const db = await initDB();
    await db.put('held_bills', {
        id: bill.id || Date.now().toString(),
        items: bill.items,
        timestamp: new Date().toISOString()
    });
};

export const getHeldBills = async () => {
    const db = await initDB();
    return await db.getAll('held_bills');
};

export const deleteHeldBill = async (id) => {
    const db = await initDB();
    await db.delete('held_bills', id);
};
