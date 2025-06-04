<x-layout :data="$data"> 
    <tr>
        <td style="text-align: center; padding-top: 48px;;">
            <img src="{{ env('APP_URL') }}/assets/img/logo.png" alt="">
        </td>
    </tr>
    <tr>
        <td style="text-align: center;">
            <p style="font-size: 32px;">🔑</p>
            <p style="font-size: 32px; font-weight: 700;">Ověřte svůj e-mail</p>
        </td>
    </tr>
    <tr>
        <td>
            <p> Dobrý den,</p>
            <p>pro dokončení registrace prosím ověřte svou e-mailovou adresu {{$data['email']}} zadáním následujícího kódu:</p>
            {{-- <p style="padding-top: 16px;">Pro ověření e-mailu zadejte následující kód:</p> --}}
        </td>
    </tr>

    <tr>
        <td style=" padding-bottom: 16px; ">
            <table align="center" width="450" style="width:450px;max-width: 450px;vertical-align:top;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px">
                <tr>
                    <td style="background-color: #FFFFFF; border-radius: 5px; text-align: center;">
                        
                        <table align="center" width="450" style="width:450px;max-width: 450px;vertical-align:top;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px">
                            <tr>
                                <td style="">
                                    <table align="center" style="border-radius: 5px; border-spacing: 8px;">
                                        <tr>
                                            @foreach (str_split($data['code'] ?? "") as $key => $code)
                                                <td style="font-size: 16px;background:#F5F5F5;width:64px;max-width: 64px;height:48px;">
                                                    <div style="padding: 8px;">{{strtoupper($code)}}</div>
                                                </td>                                      
                                            @endforeach
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td>
            <table align="center" width="450" style="width:450px;max-width: 450px;vertical-align:top;padding-top:0px;padding-right:0px;padding-bottom:24px;padding-left:0px">
                <tr>
                    
                    <td style="background-color: #FFB400; padding: 14px 24px;text-align: center;border:2px solid black;">
                        <table align="center">
                            <tr>
                                <td class="font"><a class="font" style="color:black; text-decoration: none; font-weight: 700; padding-left: 12px; border-radius: 10px;" target="_blank" href="{{env('APP_URL')}}/email-preview/{{$data['link']}}?{{http_build_query(array_merge($data, ['email' => $data['email'], 'app_url' => env('APP_URL') ?? " "] ))}}">Otevřít na webu</a> </td>
                            </tr>
                        </table>
                    </td>
                    
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td>
            <p>Pokud jste se neregistrovali, tento e-mail můžete bezpečně ignorovat.</p>
            <p style="padding-top: 16px;">Děkujeme, že jste s námi,</p>
            <p style="font-weight: 700;">winfolio</p>
        </td>
    </tr>
    




    
</x-layout>
