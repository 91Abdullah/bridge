<?php

namespace App\Http\Controllers;

use App\Imports\NumbersImport;
use App\IncomingNumber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class IncomingNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $numbers = IncomingNumber::all();
        return view('incoming.index', compact('numbers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('incoming.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $errors = $request->validate([
            'number' => 'required|numeric|unique:incoming_numbers,number'
        ]);

        $number = IncomingNumber::create([
            'number' => $request->number,
            'allowed' => $request->has('allowed') ? true : false
        ]);
        return redirect()->route('numbers.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\IncomingNumber  $incomingNumber
     * @return \Illuminate\Http\Response
     */
    public function show(IncomingNumber $incomingNumber)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\IncomingNumber  $incomingNumber
     * @return \Illuminate\Http\Response
     */
    public function edit(IncomingNumber $number)
    {
        return view('incoming.edit', compact('number'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\IncomingNumber  $incomingNumber
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, IncomingNumber $number)
    {
        $errors = $request->validate([
            'number' => [
                'required',
                'numeric',
                Rule::unique('incoming_numbers')->ignore($number->id)
            ]
        ]);

        $number = $number->update([
            'number' => $request->number,
            'allowed' => $request->has('allowed') ? true : false
        ]);
        return redirect()->route('numbers.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\IncomingNumber  $incomingNumber
     * @return \Illuminate\Http\Response
     */
    public function destroy(IncomingNumber $number)
    {
        $number->delete();
        return redirect()->route('numbers.index');
    }

    public function bulkUpload(Request $request)
    {
        Excel::import(new NumbersImport, $request->file('file'));
        return redirect()->route('numbers.index');
    }
}
