<x-auth.rima-layout titulo="Criar nova senha" subtitulo="Escolha uma senha segura para voltar ao seu restaurante.">
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div>
            <label for="email" class="text-sm font-black text-slate-700">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" autocomplete="username" autofocus required
                   class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3.5">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div>
            <label for="password" class="text-sm font-black text-slate-700">Nova senha</label>
            <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required
                   class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3.5 outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-100">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div>
            <label for="password_confirmation" class="text-sm font-black text-slate-700">Confirme a nova senha</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required
                   class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3.5 outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-100">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
        <button type="submit" class="w-full rounded-xl bg-orange-500 px-5 py-3.5 font-black text-white hover:bg-orange-600">
            Salvar nova senha
        </button>
    </form>
</x-auth.rima-layout>
