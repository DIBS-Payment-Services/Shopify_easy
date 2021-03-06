<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Easy App Settings</title>
  </head>
  <body>
  <div id="root"></div>
  <script type="text/javascript">
        window.shopifyParams = {easy_test_secret_key: "{{ $easy_test_secret_key }}",
                                easyGatewyaPassword: "{{ $gateway_password }}",
                                easy_secret_key: "{{ $easy_secret_key }}",
                                language: "{{ $language }}",
                                allowed_customer_type: "{{ $allowed_customer_type }}" ,
                                easy_merchantid: "{{ $easy_merchantid }}",
                                terms_and_conditions_url: "{{ $terms_and_conditions_url }}",
                                merchant_terms_url: "{{ $merchant_terms_url }}",
                                actionUrl: "{{ $action_url }}",
                                installGatewayRedirect: "{{ $install_gateway_redirect }}",
                                shop_url: "{{ $shop_origin }}",
                                csrf_token: "{{ csrf_token() }}"};
    </script>

    <script type="text/javascript" src="{{ asset('public/js/bundle.js') }}"></script>
  </body>
 </html>