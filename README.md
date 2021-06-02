# NETS A/S - Shopify Easy Payment App
==============================================================

|Module       | Nets Easy Payment App for Shopify
|-------------|-----------------------------------------------
|Author       | `Nets eCom`
|Prefix       | `EASY-Shopify`
|Version      | `1.5.0`
|Guide        | https://tech.nets.eu/shopmodules
|Github       | https://github.com/DIBS-Payment-Services/Shopify_easy

## INSTALLATION

Easy Integration with your shopify store 
Nets has created a plug-in module for Shopify that offers you a smoother integration.
Nets Easy for Shopify is an app that extends our gateway integration, allowing you to take payments via Nets new platform, Easy.

By following below steps you will be able to integrate easy payment in your shopify store - 
1.	Go to this link: https://easyshopify.dk
2.	Enter the shop URL 
3.	Confirm installing the app
4.	Follow the login-flow
5.	Now you are in the app, enter your keys and merchant ID  from the Easy Portal: https://portal.dibspayment.eu/. Furthermore add your terms & condition URL and choose consumer type (B2B+B2C) and lastly select your language
6.	Click on the Install button for Easy gateway
7.	Confirm installation flow
8.	Enter your merchant ID and the unique code from the app
9.	Implement Payment ID on Thank-you page and Payment ID + masked CC on email invoice 
Go to Setting->Checkout->Order processing->Additional scripts and copy-paste this in the text field:
{% for tr in checkout.transactions %}
    {% if tr.gateway == "dibs_easy_checkout" %}
    {% if tr.kind == "authorization" %} 
      Dibs Transaction id : {{tr.receipt.x_gateway_reference}}
    {% endif %}
    {% endif %}
{% endfor %}
Go to Settings->Notifications->Order confirmation and copy-paste this in the text field:
{% if transaction.gateway == "dibs_easy_checkout" %}
        Dibs Transaction id : {{transaction.receipt.x_gateway_reference}}
        {% if transaction.receipt.x_card_type %}    
             Card type: {{  transaction.receipt.x_card_type }} <br/>
        {% endif %}   
        {% if transaction.receipt.x_card_masked_pan %}
            Card Mask: {{  transaction.receipt.x_card_masked_pan }}
    {% endif %}
{% endif %}
Now you are all connected and able to handle payment through Shopify.

### Configuration

1. To configure, navigate to : Apps -> Nets Checkout
2. Locate and select Nets Easy Checkout App from the list of installed Apps
3. Install Easy checkout payment gateway by clicking on the button and complete the installation
4. Once gateway is installed, Click on Apps -> Nets checkout for setting up configurations : Merchant id, easy secret key, merchant terms url etc. 
and click on submit to save it
5. Copy the password for using it in payments 
6. Nets Checkout configuration settings in Settings -> Payments -> Nets Checkout is Active -> Click on Edit

### Contact

* Nets customer service
- Nets Easy provides support for both test and live Easy accounts. Contact information can be found here : https://nets.eu/en/payments/customerservice/

** CREATE YOUR FREE NETS EASY TEST ACCOUNT HERE : https://portal.dibspayment.eu/registration **
