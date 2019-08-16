<script src="https://unpkg.com/@shopify/app-bridge"></script>
<script>
  var AppBridge = window['app-bridge'];
  var actions = window['app-bridge'].actions;
  
  var AppBridge = window['app-bridge'];
  var createApp = AppBridge.createApp;
  var actions = AppBridge.actions;
  var Redirect = actions.Redirect;

  const apiKey = 'API key from Shopify Partner Dashboard';
  const redirectUri = '{{ $redirect_uri }}';
  const permissionUrl = '/oauth/authorize?client_id=' + '{{ $apiKey}}' + '&scope=' + '{{ $scope }}' + '&redirect_uri=' + redirectUri;

  // If the current window is the 'parent', change the URL by setting location.href
  if (window.top == window.self) {
    window.location.assign('https://' + '{{ $shopOrigin }}' + '/admin' + permissionUrl);

// If the current window is the 'child', change the parent's URL with Shopify App Bridge's Redirect action
} else {
  const app = createApp({
    apiKey: '{{ $apiKey }}',
    shopOrigin: '{{ $shopOrigin }}',
  });
    Redirect.create(app).dispatch(Redirect.Action.ADMIN_PATH, permissionUrl);
}
</script>