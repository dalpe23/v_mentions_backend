<div style="text-align: center; margin-bottom: 24px;">
    <img src="https://v-mentions.myp.com.es/VMentionsBlack.png" alt="Logo VMentions" style="max-width: 220px; height: auto;">
</div>

<div style="background: #fff; border-radius: 8px; padding: 32px; max-width: 600px; margin: 0 auto; font-family: Arial, Helvetica, sans-serif; color: #222; box-shadow: 0 2px 8px #eee;">
    <p>A continuación, las menciones registradas con valoración <b>neutra o negativa</b> en el último mes:</p>
    <ul style="padding-left: 18px;">
        @foreach ($menciones as $mencion)
            <li style="margin-bottom: 18px;">
                <b>{{ $mencion->titulo }}</b><br>
                {{ \Illuminate\Support\Str::limit($mencion->descripcion, 120) }}<br>
                Fuente: {{ $mencion->fuente }}<br>
                <a href="{{ $mencion->enlace }}" style="color: #3182ce;">Ver enlace</a><br>
                Sentimiento: <b>{{ $mencion->sentimiento }}</b> | Temática: {{ $mencion->tematica }}
            </li>
        @endforeach
    </ul>
    <div style="text-align: right; color: #888; font-size: 14px; margin-top: 32px;">VMentions</div>
</div>
