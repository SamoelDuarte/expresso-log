<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CarrierController extends Controller
{
    public function index()
    {
        $transportadoras = Carrier::get();
        return view('admin.transportadora.index', compact('transportadoras'));
    }
    public function create()
    {
        return view('admin.transportadora.create');
    }

    public function store(Request $request)
    {
        // Validação dos dados, incluindo a imagem
        $validator = Validator::make($request->all(), [
            'legal_name' => 'required|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif', // validação para a imagem
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



        // Verifica se o CNPJ é válido
        // $verifyCnpj = Utils::cnpjIsValid($request->cnpj);
        // if (!$verifyCnpj) {
        //     $mensagemErro = "CNPJ Inválido.";
        //     return back()->withErrors(['cnpj' => $mensagemErro])->withInput();
        // } else {
        //     $request->cnpj = Utils::sanitizeCpfCnpj($request->cnpj);
        // }

        // Verifica se a validação falha
        if ($validator->fails()) {
            // dd($validator);
            return redirect()
                ->route('admin.transp.create')
                ->withErrors($validator)
                ->withInput();
        }

        // Se a imagem foi enviada, armazena-a e anexa o caminho no request
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->storeAs('public/images', $imageName);
            $request->merge(['image_path' => 'storage/images/' . $imageName]);
        }



        // Cria a transportadora com os dados do request
        $transportadora = Carrier::create($request->except('_token'));

        return redirect()
            ->route('admin.transp.index')
            ->with('success', 'Transportadora Cadastrada Com Sucesso.');
    }
    public function edit($id)
    {
        $transportadora = Carrier::findOrFail($id); // Encontre a transportadora pelo ID

        return view('admin.transportadora.edit', compact('transportadora'));
    }

    public function update(Request $request, $id)
    {
        $transportadora = Carrier::findOrFail($id); // Encontre a transportadora pelo ID

        // Validação dos dados, similar à validação na função store
        $validator = Validator::make($request->all(), [
            'legal_name' => 'required|max:255',
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

        // // Verifica se o CNPJ é válido
        // $verifyCnpj = Utils::cnpjIsValid($request->cnpj);
        // if (!$verifyCnpj) {
        //     $mensagemErro = "CNPJ Inválido.";
        //     return back()->withErrors(['cnpj' => $mensagemErro])->withInput();
        // } else {
        //     $request->cnpj = Utils::sanitizeCpfCnpj($request->cnpj);
        // }

        // Verifica se a validação falha
        if ($validator->fails()) {
            return redirect()
                ->route('admin.transp.edit', ['transportadora' => $id])
                ->withErrors($validator)
                ->withInput();
        }

        // Atualiza os dados da transportadora com base nos dados do request
        $transportadora->update($request->except('_token'));

        return redirect()
            ->route('admin.transp.index')
            ->with('success', 'Transportadora Atualizada Com Sucesso.');
    }

    public function destroy($id)
    {
        $transportadora = Carrier::findOrFail($id); // Encontre a transportadora pelo ID

        // Exclua a imagem associada, se houver
        if ($transportadora->image_path) {
            Storage::delete(str_replace('storage/', 'public/', $transportadora->image_path));
        }

        // Exclua a transportadora
        $transportadora->delete();

        return redirect()
            ->route('admin.transp.index')
            ->with('success', 'Transportadora Excluída Com Sucesso.');
    }
}
