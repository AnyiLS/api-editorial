<?php

namespace App\Http\Controllers;

use App\Models\Response;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    //Register
    public function register(Request $request)
    {
        $status = false;
        $result = null;
        $user = null;

        DB::beginTransaction();
        try {
            $exist_user = User::where("document", $request->document)->first();
            if ($exist_user != null) throw new \Exception('El usuario ya existe');
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'document' => $request->document,
                'password' => Hash::make("password"),
            ]);

            Http::post('http://ec2-3-15-237-180.us-east-2.compute.amazonaws.com:3000/api/sent-email', [
                'smtp_username' => 'arelozano210914@gmail.com',
                'smtp_password' => 'jgon sapl vhhm nmec',
                'body' => "<!doctype html>
                    <html lang='en'>
                    <head>
                        <meta charset='UTF-8'>
                        <meta name='viewport'
                            content='width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0'>
                        <meta http-equiv='X-UA-Compatible' content='ie=edge'>
                        <title>Document</title>
                        <style>
                            body {
                                margin: 0;
                            }

                            .container {
                                position: relative;
                                width: 800px;
                                height: 1300px;
                                background-image: url('https://eml.com.co/Prueba-correo/response.png');
                                background-size: contain;
                                background-repeat: no-repeat;
                            }

                            .container img {
                                height: 100%;
                            }

                            .container h1 {
                                color: #fff !important;
                                width: 100%;
                                text-align: center;
                                font-family: Roboto, sans-serif;
                            }
                        </style>
                    </head>
                    <body>

                        <div>
                            <table>
                            <tbody>
                            <tr>
                            <td colspan='10'>
                            <div class='container'>
                            <h1 style='padding-top: 69%;'>ZTSAsd</h1>
                    </div>
                    </td>
                    </tr>
                    </tbody>
                    </table>

                        </div>
                    </body>
                    </html>",
                'subject' => 'url de acceso',
                'from' => 'noreply@eucerin.com',
                'to' => $user->email
            ]);

            $status = true;
            DB::commit();
        } catch (\Throwable $th) {
            $result = $th->getMessage();
            DB::rollBack();
        } finally {
            if ($status) {
                return response()->json(array(
                    "ok" => $status,
                    "data" => $user
                ), 201);
            } else {
                return response()->json(array(
                    "ok" => $status,
                    "data" => $result
                ), 500);
            }
        }
    }

    public function login(Request $request)
    {
        $exist_user = User::where("document", $request->document)->first();
        $credentials = array('email' => $exist_user->email, 'password' => 'password');

        if (Auth::attempt($credentials)) {
            $token = $exist_user->createToken("editorizacion")->accessToken;
            return response()->json(array(
                "ok" => true,
                "data" => array("token" => $token, "user" => $exist_user)
            ), 200);
        } else {
            return response()->json(array(
                "ok" => false,
                "data" => "Usuario no encontrado"
            ), 404);
        }
    }

    public function saveQuestions(Request $request)
    {
        $status = false;
        $result = null;
        $user = Auth::id();
        $response = null;

        DB::beginTransaction();
        try {
            Response::create([
                'user_id' => $user,
                'response' => $request->response,
                'question' => $request->question
            ]);

            $countAResponse = count(Response::where("question", $request->question)->where("response", 'A')->get());
            $countBResponse = count(Response::where("question", $request->question)->where("response", 'B')->get());
            $countCResponse = count(Response::where("question", $request->question)->where("response", 'C')->get());
            $countDResponse = count(Response::where("question", $request->question)->where("response", 'D')->get());

            $total = count(Response::where("question", $request->question)->get());

            $response = array(
                "a" => $countAResponse * 100 / $total,
                "b" => $countBResponse * 100 / $total,
                "c" => $countCResponse * 100 / $total,
                "d" => $countDResponse * 100 / $total,
            );

            $status = true;
            DB::commit();
        } catch (\Throwable $th) {
            $result = $th->getMessage();
            DB::rollBack();
        } finally {
            if ($status) {
                return response()->json(array(
                    "ok" => $status,
                    "data" => $response
                ), 201);
            } else {
                return response()->json(array(
                    "ok" => $status,
                    "data" => $result
                ), 500);
            }
        }
    }
}
