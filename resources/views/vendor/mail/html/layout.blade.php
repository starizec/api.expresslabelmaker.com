<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('app.name') }}</title>
    <style>
        /* Base */
        body, body *:not(html):not(style):not(br):not(tr):not(code) {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif,
                'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            position: relative;
        }

        body {
            -webkit-text-size-adjust: none;
            background-color: #ffffff;
            color: #718096;
            height: 100%;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            width: 100% !important;
        }

        p, ul, ol, blockquote {
            line-height: 1.4;
            text-align: left;
        }

        a {
            color: #3869d4;
        }

        /* Header */
        .header {
            padding: 25px 0;
            text-align: center;
        }

        .header a {
            color: #3d4852;
            font-size: 19px;
            font-weight: bold;
            text-decoration: none;
        }

        /* Body */
        .body {
            background-color: #ffffff;
            border-bottom: 1px solid #e8e5ef;
            border-top: 1px solid #e8e5ef;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .inner-body {
            background-color: #ffffff;
            margin: 0 auto;
            padding: 0;
            width: 570px;
        }

        /* Footer */
        .footer {
            margin: 0 auto;
            padding: 0;
            text-align: center;
            width: 570px;
        }

        .footer p {
            color: #b0adc5;
            font-size: 12px;
            text-align: center;
        }

        /* Tables */
        .table table {
            margin: 30px auto;
            width: 100%;
        }

        .table th {
            border-bottom: 1px solid #edeff2;
            margin: 0;
            padding-bottom: 8px;
        }

        .table td {
            color: #74787e;
            font-size: 15px;
            line-height: 18px;
            margin: 0;
            padding: 10px 0;
        }

        /* Buttons */
        .button {
            border-radius: 3px;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16);
            color: #fff;
            display: inline-block;
            text-decoration: none;
            -webkit-text-size-adjust: none;
        }

        .button-blue, .button-primary {
            background-color: #2d3748;
            border-top: 10px solid #2d3748;
            border-right: 18px solid #2d3748;
            border-bottom: 10px solid #2d3748;
            border-left: 18px solid #2d3748;
        }

        /* Subcopy */
        .subcopy {
            border-top: 1px solid #e8e5ef;
            margin-top: 25px;
            padding-top: 25px;
        }

        .subcopy p {
            font-size: 14px;
        }
    </style>
</head>
<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <!-- Header -->
                    {{ $header ?? '' }}

                    <!-- Body -->
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <!-- Body content -->
                                <tr>
                                    <td class="content-cell">
                                        {{ Illuminate\Mail\Markdown::parse($slot) }}

                                        {{ $subcopy ?? '' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    {{ $footer ?? '' }}
                </table>
            </td>
        </tr>
    </table>
</body>
</html> 