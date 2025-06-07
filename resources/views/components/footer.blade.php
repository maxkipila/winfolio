<table align="center" width="450"
    style="width:450px;max-width: 450px;vertical-align:top;color:black;background:#F5F5F5;padding-top:48px;padding-right:0px;padding-bottom:0px;padding-left:0px">
    <tr>
        <td>
            <table align="center" width="450" style="width:450px;max-width:450px;">
                <tr>
                    <td style="width: 80%;">
                        <img src="{{ env('APP_URL') }}/assets/img/logo.png" alt="">
                    </td>
                    
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding-top: 12px;">
            <table align="center" width="450"
                style="width:450px;max-width:450px;border-bottom: 1px solid rgba(255, 255, 255, 0.16);border-top: 1px solid rgba(255, 255, 255, 0.16);">
                <tr>
                    <td style="width:80%; padding:16px 0 16px 0; "><a style="color:black;text-decoration: none;font-weight: 700;"
                            href="#">Buďme ve spojení</a></td>
                    <td style="text-align: right;">
                        <a href="https://www.facebook.com"><img
                                src="{{ env('APP_URL') }}/assets/img/email-facebook.png" alt=""></a>
                        <a href="https://www.instagram.com"><img
                                src="{{ env('APP_URL') }}/assets/img/email-instagram.png" alt=""></a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table align="center" width="450" style="width:450px;max-width:450px;">
                <tr>
                    <td>
                        <p>
                            @if (!($data['no_subscribe'] ?? false))
                            Tento e-mail jsme Vám zaslali na základě Vaší registrace nebo aktivity na platformě winfolio. Pokud si nepřejete, aby Vám winfolio nadále zasílalo jakákoli reklamní, informační nebo investičně orientovaná sdělení (včetně novinek, cenových upozornění či tipů na sety), můžete se z odběru kdykoli odhlásit kliknutím na tlačítko níže.
                            <br>
                            <br>
                            Vaše e-mailová adresa bude neprodleně odstraněna z našeho seznamu pro zasílání marketingových sdělení. Jakmile proběhnou potřebné změny v našem systému, veškerá sdělení označená jako obchodní nebo propagační budou automaticky blokována.
                            @endif
                            <br>
                            <br>
                            Klikněte <a href="#">zde</a>, pokud si nepřejete dostávat od společnosti winfolio žádná obchodní sdělení.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table align="center" width="450" style="width:450px;max-width:450px;">
                <tr>
                    <td>
                        <p>© 2025 winfolio - Kostky tvého investičního impéria padají správně</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
