<!doctype html>
<html>


    <style> 
        
      #footer {
           position:absolute;
           bottom:0;
           width:100%;
           height:60px;   /* Height of the footer */
     }
        
    </style> 

    <body>
        <div id="install-form">
            <link rel="stylesheet" href="https://unpkg.com/@shopify/polaris@4.1.0/styles.min.css" />
        </div>
        <div style="--top-bar-background:#00848e; --top-bar-color:#f9fafb; --top-bar-background-lighter:#1d9ba4;">
          <div class="Polaris-Page">
             <div class="Polaris-Page__Content" style="padding-top:25%;">
              <div class="Polaris-Layout">
                <div class="Polaris-Layout__AnnotatedSection">
                  <div class="Polaris-Layout__AnnotationWrapper">
                    <div class="Polaris-Layout__AnnotationContent">
                      <div style="margin-left : 40%;">  
                        <img src="https://tech.dibspayment.com/sites/tech/files/pictures/LOGO/DIBS/PNG/DIBS_Easy_Logo_pos_Black.png" width="170" height="66">  
                      </div>
                      <form action = "/installinit" >
                        <div class="Polaris-FormLayout">
                        <div class="Polaris-FormLayout__Item">
                            <div class="">
                              <div class="Polaris-Labelled__LabelWrapper">
                                <div class="Polaris-Label"><label id="TextField1Label" for="TextField1" class="Polaris-Label__Text">Your shop url: <b>https://your-shop.myshopify.com </b></label></div>
                              </div>
                              <div class="Polaris-TextField Polaris-TextField--@if ($errors->any())error @endif">
                                <input id="TextField1" class="Polaris-TextField__Input" aria-describedby="TextField1HelpText" aria-invalid="false"  placeholder="https://your-shop.myshopify.com">
                                <div class="Polaris-TextField__Backdrop"></div>
                              </div>
                              <div class="Polaris-Labelled__HelpText" id="TextField1HelpText"><span>  Enter your Shopify URL address in order to start installing Easy. Click <a class="Polaris-Link"  href="https://www.youtube.com/watch?v=yqMVqmoO3BM" target="_blank">HERE</a> to see our video guide.  </span></div>
                            </div>
                          </div>
                         <div class="Polaris-FormLayout__Item"><button type="submit" class="Polaris-Button Polaris-Button--primary"> <span class="Polaris-Button__Content"><span class="Polaris-Button__Text">Submit</span></span></button></div>
                        </div><span class="Polaris-VisuallyHidden"><button class="Polaris-Button Polaris-Button--primary" type="submit" aria-hidden="true">Submit</button></span>
                     </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    </body>
</html>