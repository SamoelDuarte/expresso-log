<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarrierController extends Controller
{
    public function index()
    {
        return view('admin.transportadora.index');
    }

    public function create()
    {
        return view('admin.transportadora.create');
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'legal_name' => 'required|max:255',
            'image' => 'image|mimes:jpeg,png,jpg,gif',
            'cnpj' => 'required',
            'phone' => 'required',
            'trade_name' => 'required|max:255',
            'state_registration' => 'required|max:255',
            'rntrc' => 'required|max:9',
            'zip_code' => 'required|max:255',
            'state' => 'required|max:255',
            'city' => 'required|max:255',
            'name' => 'required|max:9',
            'email' => 'required|email|max:255',
            'phone1' => 'required|max:255',
        ]);

        $verifyCnpj = Utils::cnpjIsValid($request->cnpj);


        if (!$verifyCnpj) {

            $mensagemErro = "CNPJ Inválido.";
            return
                back()->withErrors(['cnpj' => $mensagemErro])
                ->withInput();
        }

        if ($validator->fails()) {

            return redirect()
                ->route('admin.transp.create') // Redirecione de volta ao formulário de criação
                ->withErrors($validator)
                ->withInput();
        }

        $transportadora = Carrier::create($request->except('_token'));



        return redirect()
            ->route('admin.transp.index') // Redirecione de volta ao formulário de criação
            ->with('success', 'Tranportadora Cadastrada Com Sucesso.');
    }
}
