<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    //List all the clients for the authenticated coach
    public function index(Request $request)
    {
        $clients = Client::where('coach_id', $request->user()->id)->with('user')->paginate(10);
        return response()->json($clients, 200);
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone_number' => 'required|string|max:12',
                'date_of_birth' => 'required|date',
                'notes' => 'string|nullable',
            ]);

            $client = DB::transaction(function () use ($validated, $request) {

                $clientUser = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make('Test@1234'),
                    'role' => collect(config('enums.user_roles'))->search('client') ?? 2,
                ]);

                return  Client::create([
                    'coach_id' => $request->user()->id,
                    'user_id' => $clientUser->id,
                    'phone_number' => $validated['phone_number'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'notes' => $validated['notes'],
                ]);
            });

            //TODO: Send Email notification to client for their new login details

            return response()->json(['message' => 'Client created successfully', 'client' => $client], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed' ,'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while creating client'. $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Client $client)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $client->user_id,
                'phone_number' => 'sometimes|string|max:15',
                'date_of_birth' => 'sometimes|date',
                'notes' => 'nullable|string',
            ]);

            if (isset($validated['name']) || isset($validated['email'])) {
                $client->user->update([
                    'name' => $validated['name'] ?? $client->user->name,
                    'email' => $validated['email'] ?? $client->user->email,
                ]);
            }

            // Update the client profile
            $client->update(array_filter([
                'phone_number' => $validated['phone_number'] ?? $client->phone_number,
                'date_of_birth' => $validated['date_of_birth'] ?? $client->date_of_birth,
                'notes' => $validated['notes'] ?? $client->notes,
            ]));

            return response()->json([
                'message' => 'Client updated successfully',
                'client' => $client->load('coach'),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed' ,'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while creating client'. $e->getMessage()], 500);
        }

    }

    public function destroy(Client $client)
    {
        DB::transaction(function () use ($client) {

            // Delete the client user account as well as the client profile.

            $client->user()->delete();
            $client->delete();
        });

        return response()->json(['message' => 'Client deleted successfully'], 200);
    }

}
