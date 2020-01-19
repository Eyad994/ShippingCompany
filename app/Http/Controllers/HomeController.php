<?php

namespace App\Http\Controllers;

use App\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        $addresses = Address::all();

        dd($addresses);
        $arr = array();

        // return all addresses with status active or deleted

        dd(Address::onlyTrashed()->get());
        dd(Address::where('id', '9c13695e-3zc2-4489-be74-51d16006db6a')->delete());



        // return all addresses with countries
        /*foreach ($addresses as $address) {
            $arr[] = [
                'uuid' => $address['id'],
                'country' => $address->country->country,
                'country_uuid' => $address->country->id,
                'city' => $address['city'],
                'street' => $address['street'],
                'postal_code' => $address['postal_code'],
                'cellular_number' => $address['cellular_number'],
                'creation date' => $address['created_at'],
                'update date' => $address['updated_at']
            ];
        }*/
    }

    public function allAddresses()
    {
        $addresses = Address::withTrashed()->get();
        $arr = array();
        foreach ($addresses as $address) {
            $arr[] = [
                'uuid' => $address['id'],
                'country' => $address->country->country,
                'country_uuid' => $address->country->id,
                'city' => $address['city'],
                'street' => $address['street'],
                'postal_code' => $address['postal_code'],
                'cellular_number' => $address['cellular_number'],
                'creation date' => $address['created_at'],
                'update date' => $address['updated_at']
            ];
        }

        return $arr;
    }

    public function allAddressWithStatus($status)
    {
        if ($status == 'deleted')
        {
            $arr = array();
            $addresses = Address::onlyTrashed()->get();

            foreach ($addresses as $address) {
                $arr[] = [
                    'uuid' => $address['id'],
                    'country' => $address->country->country,
                    'country_uuid' => $address->country->id,
                    'city' => $address['city'],
                    'street' => $address['street'],
                    'postal_code' => $address['postal_code'],
                    'cellular_number' => $address['cellular_number'],
                    'creation date' => $address['created_at'],
                    'update date' => $address['updated_at']
                ];
            }
            return $arr;
        }

        else if ($status == 'active') {
            $addresses = Address::where('deleted_at', null)->get();
            $arr = array();
            foreach ($addresses as $address) {
                $arr[] = [
                    'uuid' => $address['id'],
                    'country' => $address->country->country,
                    'country_uuid' => $address->country->id,
                    'city' => $address['city'],
                    'street' => $address['street'],
                    'postal_code' => $address['postal_code'],
                    'cellular_number' => $address['cellular_number'],
                    'creation date' => $address['created_at'],
                    'update date' => $address['updated_at']
                ];
            }
            return $arr;
        }
    }

    public function address(Request $request)
    {

        if ($request->id != null)
        {
            $rules = array(
                'id' => 'required|exists:addresses,id',
                'country' => 'required|exists:countries,id',
                'city' => 'required|min:3|max:30',
                'neighborhood' => 'nullable|min:3|max:30',
                'street' => 'required|min:3|max:30',
                'postal_code' => 'required|numeric',
                'cellular_number' => 'required|regex:/(07)[7-9]{1}[0-9]{7}/'
            );

            $validator = Validator::make(Input::all(), $rules);
            if ($validator->fails()) {
                return ['status' => 422, 'errors' => $validator->errors()];
            }

             $address = Address::find($request->id);
             $address->update([
                'country_id' => $request->country,
                'city' => $request->city,
                'neighborhood' => $request->neighborhood,
                'street' => $request->street,
                'postal_code' => $request->postal_code,
                'cellular_number' => $request->cellular_number,
                 'updated_at' => now()
            ]);

            return response()->json([
                'message' => 'Updated successfully!',
                'data' => [
                    'country' => $address->country->country,
                    'city' => $address['city'],
                    'neighborhood' => $address['neighborhood'],
                    'street' => $address['street'],
                    'postal_code' => $address['postal_code'],
                    'cellular_number' => $address['cellular_number'],
                    'creation_date' => $address['created_at'],
                    'modification_date' => $address['updated_at']
                ]
            ], 200);

        }
        else
        {
            $rules = array(
                'country' => 'required|exists:countries,id',
                'city' => 'required|min:3|max:30',
                'neighborhood' => 'nullable|min:3|max:30',
                'street' => 'required|min:3|max:30',
                'postal_code' => 'required|numeric',
                'cellular_number' => 'required|regex:/(07)[7-9]{1}[0-9]{7}/'
            );

            $validator = Validator::make(Input::all(), $rules);
            if ($validator->fails()) {
                return ['status' => 422, 'errors' => $validator->errors()];
            }


            $address = Address::create([
                'country_id' => $request->country,
                'city' => $request->city,
                'neighborhood' => $request->neighborhood,
                'street' => $request->street,
                'postal_code' => $request->postal_code,
                'cellular_number' => $request->cellular_number,
                'updated_at' => now()
            ]);
            return response()->json([
                'message' => 'Created successfully!',
                'data' => [
                    'country' => $address->country->country,
                    'city' => $address['city'],
                    'neighborhood' => $address['neighborhood'],
                    'street' => $address['street'],
                    'postal_code' => $address['postal_code'],
                    'cellular_number' => $address['cellular_number'],
                    'creation_date' => $address['created_at'],
                    'modification_date' => $address['updated_at']
                ]
            ], 200);

        }
    }

    public function destroy($id)
    {
        $address = Address::find($id);
        if ($address == null)
        {
            return response()->json(['message' => 'No address'], 204);
        }
        else if (Address::where('id', $id)->delete())
        {
            return response()->json(['message' => 'Deleted successfully'], 200);
        }


    }
}
