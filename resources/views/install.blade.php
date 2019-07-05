<!doctype html>
<html>
    <body>
        <div id="install-form">
            <img src="https://tech.dibspayment.com/sites/tech/files/pictures/LOGO/DIBS/PNG/DIBS_Easy_Logo_pos_Black.png" width="170" height="66">
            <form  action="/installinit"> 
               <input id="shop-url-input" name="shop" class="@if ($errors->any()) danger @endif" placeholder="https://your-shop.myshopify.com" type="text" width="135" />
               <input type="submit"  class="btn primary" value="Submit">
           </form>
            Enter your Shopify URL address in order to start installing Easy. Click 
                <a href="https://drive.google.com/file/d/1FrdMi0DIBrP8sEW6_laFH0VvhcxKAHyj/view">HERE</a> 
            to see our video guide
      </div>
    </body>
        <style>
            body {
                text-align: center;
                 background: #ebeef0;
            }
            .danger {
                border: 1px solid;
                border-color: red;
            }
            #install-form {
               margin:0 auto; 
               margin-top: 20%;
               width: 50%;
            }
            #shop-url-input {
                width: 35%;
                height: 35px;
            }
            
            
            
         .btn {
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            cursor: pointer;
            display: inline-block;
            height: 32px;
            line-height: 30px;
            padding: 0 15px;
            font-size: 13px;
            border-radius: 4px;
            text-decoration: none;
            white-space: nowrap;
            text-transform: none;
            vertical-align: middle;
            -webkit-appearance: none;
            margin: 0;
        }

    .btn:active {
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2) inset;
    }

    /* modifier: primary */
    .btn.primary {
        background-color: #479ccf;
        border: 1px solid #479ccf;
        color: #FFFFFF;
    }

    .btn.primary:hover, .btn.primary:active {
        background-color: #4293C2;
        border: #479ccf 1px solid;
        color: #FFFFFF;
        text-decoration: none;
    }

    /* modifier: disabled */

    .btn.disabled, .btn.disabled:hover, .btn.disabled:focus, .btn.disabled:active, .btn.disabled:link, .btn.disabled:visited {
        cursor: default;
        box-shadow: none;
        background: #fafbfc;
        color: #c3cfd8;
        border: 1px solid #d3dbe2;
        text-decoration: none;
    }




    input {
        border-radius: 3px;
        height: 32px;
        margin-bottom: 15px;
        max-width: 100%;
        font-size: 13px;
        padding-left: 10px;
        padding-right: 10px;
        border: 1px solid #ccc;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
    }

    input[type=text] {
        width: 100%;
        font-size: 15px;
    }

    input {
        height: 28px;
    }

    input::-webkit-input-placeholder {
       color: #c3cfd8;
    }

    input:-moz-placeholder { /* Firefox 18- */
       color: #c3cfd8;
    }

    input::-moz-placeholder {  /* Firefox 19+ */
       color: #c3cfd8;
    }

    input:-ms-input-placeholder {
       color: #c3cfd8;
    }

    input[type=radio], input[type=checkbox] {
        float: left;
        height: auto;
        line-height: 1;
        width: auto;
        max-width: none;
        margin: 0 10px 5px 0;
        border: none;
        padding: 0;
        vertical-align: baseline;
        display: inline-block;
    }
        
        </style>
</html>