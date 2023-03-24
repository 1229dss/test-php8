<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRegister;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

use App\Models\User;

class UserController extends Controller
{
    public function login()
    {
        return view('auth/login');
    }

    // 会員登録画面
    public function register()
    {
        return view('auth/register');
    }

    // 会員登録処理
    public function user_register(UserRegister $request)
    {
        $request['password'] = Hash::make($request['password']);

        // フォルダモデルのインスタンスを作成する
        $user = new User();
        // タイトルに入力値を代入する
        $user->fill([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => $request['password'],
        ]);
        // インスタンスの状態をデータベースに書き込む
        $user->save();

        return redirect('login');
    }

    // ログイン認証
    public function certification(Request $request)
    {
        $check = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::attempt($check)) {
            // ログインユーザーを取得する
            $user = Auth::user();

            // ログインユーザーに紐づくフォルダを一つ取得する
            $folder = $user->folders()->first();

            // まだ一つもフォルダを作っていなければホームページをレスポンスする
            if (is_null($folder)) {
                return view('home');
            }

            // フォルダがあればそのフォルダのタスク一覧にリダイレクトする
            return redirect()->route('tasks.index', [
                'id' => $folder->id,
            ]);
        } else {
            return redirect('login');
        }
    }
    
    // ログアウト
    public function logout(Request $request) 
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('login');
    }

    // パスワードリセット画面
    public function password_reset_form() 
    {
        return view('auth/passwords/email');
    }


    // パスワードリセットメール送信処理
    public function forgot_password(Request $request) 
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    // パスワードリセット処理
    public function reset_password(Request $request) 
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required | email',
            'password' => 'required',
            'password_confirmation' => 'required | same:password',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('user_login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
