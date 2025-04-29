<tr>
    <td style="text-align: center;">
        <div style="padding: 16px 0 16px 0;">
            Nezobrazuje se Vám e-mail správně? – <a style="text-decoration: underline; color:black;" href="{{env('APP_URL')}}/email-preview/{{$data['link']}}?{{http_build_query(array_merge($data, ['email' => $data['email'], 'app_url' => env('APP_URL') ?? " "] ))}}">Zobrazit email v prohlížeči</a>
        </div>
    </td>
</tr>

