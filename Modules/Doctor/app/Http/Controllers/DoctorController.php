<?php

namespace Modules\Doctor\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Medicine;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    /**
     * Patient CRUD Methods
     */
    public function getPatients()
    {
        return response()->json(['data' => Patient::orderBy('created_at', 'desc')->get()]);
    }

    public function storePatient(Request $request)
    {
        $validated = $request->validate([
            'mrn' => 'required|string|unique:patients,mrn',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'nic' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'date_of_birth' => 'nullable|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'allergies' => 'nullable|string'
        ]);

        $validated['uuid'] = Str::uuid();

        $patient = Patient::create($validated);

        return response()->json(['message' => 'Patient created successfully', 'data' => $patient]);
    }

    public function updatePatient(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $validated = $request->validate([
            'mrn' => 'required|string|unique:patients,mrn,' . $patient->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'nic' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'date_of_birth' => 'nullable|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'allergies' => 'nullable|string'
        ]);

        $patient->update($validated);

        return response()->json(['message' => 'Patient updated successfully', 'data' => $patient]);
    }

    public function deletePatient($id)
    {
        $patient = Patient::findOrFail($id);
        $patient->delete();

        return response()->json(['message' => 'Patient deleted successfully']);
    }

    /**
     * Get queue of consultations for today.
     */
    public function queue(Request $request)
    {
        $consultations = Consultation::with('patient')
            ->whereDate('created_at', today())
            // ->where('doctor_id', $request->user()->id) // simplified for MVP
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['data' => $consultations]);
    }

    /**
     * Start a consultation
     */
    public function startConsultation(Request $request, $id)
    {
        $consultation = Consultation::where('uuid', $id)->firstOrFail();
        $consultation->update(['status' => 'in_progress']);

        return response()->json(['message' => 'Consultation started', 'data' => $consultation]);
    }

    /**
     * Prescribe and complete consultation
     */
    public function prescribe(Request $request, $id)
    {
        $request->validate([
            'symptoms' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
            'prescriptions' => 'array',
            'prescriptions.*.medicine_id' => 'required|exists:medicines,id',
            'prescriptions.*.dosage' => 'required|string',
            'prescriptions.*.frequency' => 'required|string',
            'prescriptions.*.duration' => 'required|string',
        ]);

        $consultation = Consultation::where('uuid', $id)->firstOrFail();

        DB::transaction(function () use ($request, $consultation) {
            $consultation->update([
                'status' => 'completed',
                'symptoms' => $request->symptoms,
                'diagnosis' => $request->diagnosis,
                'notes' => $request->notes
            ]);

            if ($request->has('prescriptions') && count($request->prescriptions) > 0) {
                $prescription = Prescription::create([
                    'uuid' => Str::uuid(),
                    'consultation_id' => $consultation->id,
                    'patient_id' => $consultation->patient_id,
                    'doctor_id' => $request->user()->id ?? 1, // Fallback for MVP if no auth
                    'notes' => $request->notes
                ]);

                foreach ($request->prescriptions as $item) {
                    PrescriptionItem::create([
                        'uuid' => Str::uuid(),
                        'prescription_id' => $prescription->id,
                        'medicine_id' => $item['medicine_id'],
                        'dosage' => $item['dosage'],
                        'frequency' => $item['frequency'],
                        'duration' => $item['duration'],
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Consultation completed successfully']);
    }

    /**
     * Search medicines for prescription
     */
    public function searchMedicines(Request $request)
    {
        $query = $request->get('q', '');
        
        $medicines = Medicine::where('name', 'like', "%{$query}%")
            ->orWhere('generic_name', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json(['data' => $medicines]);
    }
}
