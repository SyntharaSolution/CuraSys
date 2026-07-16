<?php

namespace Modules\Pharmacy\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PharmacyController extends Controller
{
    /**
     * Categories CRUD
     */
    public function getCategories()
    {
        return response()->json(['data' => \App\Models\MedicineCategory::orderBy('name')->get()]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:medicine_categories,name',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        $validated['uuid'] = (string) \Illuminate\Support\Str::uuid();
        $category = \App\Models\MedicineCategory::create($validated);

        return response()->json(['message' => 'Category created', 'data' => $category]);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = \App\Models\MedicineCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:medicine_categories,name,' . $category->id,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        $category->update($validated);

        return response()->json(['message' => 'Category updated', 'data' => $category]);
    }

    public function deleteCategory($id)
    {
        \App\Models\MedicineCategory::findOrFail($id)->delete();
        return response()->json(['message' => 'Category deleted']);
    }

    /**
     * Medicines CRUD
     */
    public function getSubcategories()
    {
        $subcategories = \App\Models\Medicine::whereNotNull('subcategory')
            ->where('subcategory', '!=', '')
            ->distinct()
            ->pluck('subcategory');
        return response()->json(['data' => $subcategories]);
    }

    public function getMedicines()
    {
        $medicines = \App\Models\Medicine::with('category')->orderBy('name')->get();
        return response()->json(['data' => $medicines]);
    }

    public function storeMedicine(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'nullable|string|unique:medicines,barcode',
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'category_id' => 'nullable',
            'category_name' => 'nullable|string|max:255',
            'subcategory' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:50',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive'
        ]);

        $categoryId = $request->category_id;
        if (empty($categoryId) && !empty($request->category_name)) {
            $cat = \App\Models\MedicineCategory::firstOrCreate(
                ['name' => $request->category_name],
                ['uuid' => (string) \Illuminate\Support\Str::uuid(), 'status' => 'active']
            );
            $categoryId = $cat->id;
        }

        $validated['category_id'] = $categoryId;
        unset($validated['category_name']); // not database field

        $validated['uuid'] = (string) \Illuminate\Support\Str::uuid();
        $medicine = \App\Models\Medicine::create($validated);

        return response()->json(['message' => 'Medicine created', 'data' => $medicine]);
    }

    public function updateMedicine(Request $request, $id)
    {
        $medicine = \App\Models\Medicine::findOrFail($id);

        $validated = $request->validate([
            'barcode' => 'nullable|string|unique:medicines,barcode,' . $medicine->id,
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'category_id' => 'nullable',
            'category_name' => 'nullable|string|max:255',
            'subcategory' => 'nullable|string|max:255',
            'strength' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:50',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive'
        ]);

        $categoryId = $request->category_id;
        if (empty($categoryId) && !empty($request->category_name)) {
            $cat = \App\Models\MedicineCategory::firstOrCreate(
                ['name' => $request->category_name],
                ['uuid' => (string) \Illuminate\Support\Str::uuid(), 'status' => 'active']
            );
            $categoryId = $cat->id;
        }

        $validated['category_id'] = $categoryId;
        unset($validated['category_name']); // not database field

        $medicine->update($validated);

        return response()->json(['message' => 'Medicine updated', 'data' => $medicine]);
    }

    public function deleteMedicine($id)
    {
        \App\Models\Medicine::findOrFail($id)->delete();
        return response()->json(['message' => 'Medicine deleted']);
    }
}
