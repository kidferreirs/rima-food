<x-auth.rima-layout titulo="Recuperar senha" subtitulo="Informe seu e-mail e enviaremos um link para você criar uma nova senha.">
    @if(session('status'))
        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-700">{{ session('status') }}</div>
    @endif
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="text-sm font-black text-slate-700">E-mail da sua conta</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus required
                   class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3.5 outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-100">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <button type="submit" class="w-full rounded-xl bg-orange-500 px-5 py-3.5 font-black text-white hover:bg-orange-600">
            Enviar link de recuperação
        </button>
        <a href="{{ route('login') }}" class="flex justify-center text-sm font-bold text-slate-500 hover:text-orange-600">← Voltar ao login</a>
    </form>
</x-auth.rima-layout>
