<?php

namespace App\Http\Controllers;

use App\PinCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PinCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $codes = PinCode::all();
        return view('pins.index', compact('codes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pins.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:pin_codes,code|numeric|digits_between:4,10',
            'branch_name' => 'required|string'
        ]);

        $code = PinCode::create($request->all());

        return redirect()->route('pinCodes.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PinCode  $pinCode
     * @return \Illuminate\Http\Response
     */
    public function show(PinCode $pinCode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PinCode  $pinCode
     * @return \Illuminate\Http\Response
     */
    public function edit(PinCode $pinCode)
    {
        return view('pins.edit', compact('pinCode'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PinCode  $pinCode
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PinCode $pinCode)
    {
        $request->validate([
            'code' => [
                'required',
                'numeric',
                'digits_between:4,10',
                Rule::unique('pin_codes')->ignore($pinCode->id)
            ],
            'branch_name' => 'required|string'
        ]);

        $pinCode->update($request->all());
        return redirect()->route('pinCodes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PinCode  $pinCode
     * @return \Illuminate\Http\Response
     */
    public function destroy(PinCode $pinCode)
    {
        $pinCode->delete();
        return redirect()->route('pinCodes.index');
    }
}
