<x-auth.rima-layout titulo="Bem-vindo de volta" subtitulo="Entre para administrar seu negócio.">
    <x-auth-session-status class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700" :status="session('status')" />
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="text-sm font-black text-slate-700">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" autofocus required
                   class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3.5 outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-100">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div>
            <div class="flex items-center justify-between gap-4">
                <label for="password" class="text-sm font-black text-slate-700">Senha</label>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-bold text-orange-600 hover:text-orange-700">Esqueci minha senha</a>
                @endif
            </div>
            <input id="password" type="password" name="password" autocomplete="current-password" required
                   class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3.5 outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-100">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <label class="flex items-center gap-3 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-orange-500 focus:ring-orange-500">
            <span>Permanecer conectado</span>
        </label>
        <button type="submit" class="w-full rounded-xl bg-orange-500 px-5 py-3.5 font-black text-white hover:bg-orange-600">Entrar</button>
        <div class="border-t border-slate-200 pt-5 text-center">
            <p class="text-sm text-slate-500">Ainda não possui sua loja?</p>
            <a href="{{ route('saas.cadastro') }}" class="mt-2 inline-flex font-black text-orange-600 hover:text-orange-700">
                Criar pelo formulário inteligente
            </a>
        </div>
    </form>
</x-auth.rima-layout>
