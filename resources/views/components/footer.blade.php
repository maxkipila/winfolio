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
                            Tento e-mail jsem Vám zaslali na základě Vaší objednávky. V případě, že si nepřejete, aby Vám společnost winfolio zasílala jakákoli reklamní, propagační či obchodní sdělení, můžete se z odběru těchto e-mailů odhlásit kliknutím na tlačítko níže. Vaše e-mailová adresa bude okamžitě odstraněna z našeho centrálního seznamu pro zasílání obchodních sdělení, a jakmile budou provedeny nezbytné úpravy v našem systému pro zasílání obchodních sdělení, budou veškerá obchodní sdělení od winfolio označená jako reklamní či propagační automaticky blokována. 
                            @endif
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
                        <p>© 2025 winfolio</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
