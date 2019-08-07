<html>
    <head>
        <link rel="stylesheet" type="text/css" href="http://seaff.microapps.com/css/seaff.css">
        <script src="https://cdn.shopify.com/s/assets/external/app.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"></script>
    </head>
<div id="dibs-easy-settings-form">    
@if ($message = Session::get('success'))
    <div class="box notice"><i class="ico-notice"></i>{{$message}}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <div class="box warning"><i class="ico-warning"></i>{{ $error }}</div>
            @endforeach
        </ul>
    </div>
@endif
<br/>
<a class="btn primary" href="{{ $gateway_install_link }}" target="_blank">Install Easy Checkout Gateway</a>
<br/>
<br/>
<span id="dibs-easy-settings-form-password">Gateway password:</span> 
<br/>
<span>{{$gateway_password}}</span> <br/>

<div id="dibs-easy-settings-form-password-notice">
<span class="icon ico-help-blue"></span><span>Use this password in <b>Settings->Payment providers->Dibs Easy Checkout</b></span>
</div>

<br/>
<form action="/postForm" method="POST">
      
      Merchantid:<br>
      <input type="text" name="easy_merchantid" value="{{ $easy_merchantid }}"><br>
      Live secret key:<br>
      <input type="text" name="easy_secret_key" value="{{ $easy_secret_key }}"><br>
      Test secret key:<br>
      <input type="text" name="easy_test_secret_key" value="{{ $easy_test_secret_key }}"><br><br>
      Terms and conditions url:<br>
      <input type="text" name="terms_and_conditions_url" value="{{ $terms_and_conditions_url }}"><br><br>
      
      Language: <br>
      <select name="language">
        <option value="">--Please choose an option--</option>
            @foreach($lang as $key=>$value)
                <option value="{{$key}}"  {{ $key == $language ? "selected=selected" : '' }}   >{{ $value }}</option>
            @endforeach;
      </select>
      <br/>
      
      Allowed customer type: <br/>
      <select name="allowed_customer_type">
        <option value="">--Please choose an option--</option>
          @foreach($act as $key => $value)
             <option value="{{$key}}"  {{ $key == $allowed_customer_type ? "selected=selected" : '' }}   >{{ $value }}</option>
          @endforeach;
       </select>
      <input type="hidden" name="shop_url" value="{{ $shop_url }}">
       <br/>
       <br/>
    <input type="submit" class="btn primary" value="Submit">
</form>
<a href="https://{{ $shop_origin }}/admin/apps"> << back  </a>
</div>
    <style>
        #dibs-easy-settings-form{
            width:505;
            margin: 20px;
            background-color: #fafcfc;
            padding: 20px;
        }
        
        #dibs-easy-settings-form-password {
            font-weight: bold;
            font-size:medium;
        }
        
        #dibs-easy-settings-form-password-notice {
            margin-top: 20px;
            font-style: oblique;
        }
    </style>
</html>