@component('mail::message')
# ¡¡Nueva Alerta Recibida Para Añadir!!

**Keywords:** {{ $alertaData['keywords'] }}

**Idioma:** {{ $alertaData['idioma'] }}

**ID del usuario:** {{ $alertaData['user_id'] }}

@component('mail::button', ['url' => 'https://v-mentions.myp.com.es/alertas'])
Ver Alertas
@endcomponent

VMentions  
@endcomponent
