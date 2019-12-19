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
                      <form action="key" method="POST">
                        <div class="Polaris-FormLayout">
                        <div class="Polaris-FormLayout__Item">
                            <div class="">
                              <div class="Polaris-Labelled__LabelWrapper">
                                  curl -H "Authorization: {{ $decrypted_key }}" &nbsp; https://api.dibspayment.eu/v1/payments/
                              </div>
                              <div class="Polaris-TextField Polaris-TextField--@if ($errors->any())error @endif">
                                  <input id="TextField1" class="Polaris-TextField__Input" aria-describedby="TextField1HelpText" autocomplete="off" aria-invalid="false" name="key">
                                <div class="Polaris-TextField__Backdrop"></div>
                              </div>
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