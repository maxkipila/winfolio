<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
</head>

<body class="font" style="background-color: #FFF; font-family: Aspekta, sans-serif;">

    <table style="background-color: #FFF;" class="" width="100%" cellpadding="0" cellspacing="0"
        role="presentation">
        <tr>
            <td align="center" width="570"
                style="width:570px;max-width:570px;vertical-align:top;color:black;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px">
                <table class="content " width="100%" cellpadding="0" cellspacing="0" role="presentation">

                    <table align="center" width="570"
                        style="width:570px;max-width:570px;vertical-align:top;color:black;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px; border-bottom: 1px solid #DEDFE5;">
                        <x-header :data="$data" />
                    </table>

                    <table align="center" width="570"
                        style="width:570px;max-width:570px;vertical-align:top;color:black;padding-top:0px;padding-right:0px;padding-bottom:48px;padding-left:0px">
                        <tr>
                            <td>
                                <table align="center" width="450" style="width:450px;max-width: 450px;">
                                    {{ $slot }}
                                </table>
                            </td>
                        </tr>
                    </table>

                    <table align="center" width="570"
                        style="width:570px;max-width:570px;vertical-align:top;color:white;background:#F5F5F5;padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px">
                        <tr>
                            <td>
                                <table align="center" width="450" style="width:450px;max-width: 450px;">
                                    <x-footer :data="$data" />
                                </table>
                            </td>
                        </tr>
                    </table>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
