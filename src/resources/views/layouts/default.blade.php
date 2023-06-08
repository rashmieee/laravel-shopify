<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \Osiset\ShopifyApp\Util::getShopifyConfig('app_name') }}</title>
        @yield('styles')
    </head>

    <body>
        <div class="app-wrapper">
            <div class="app-content">
                <main role="main">
                    @yield('content')
                </main>
            </div>
        </div>

        @if(\Osiset\ShopifyApp\Util::getShopifyConfig('appbridge_enabled') && \Osiset\ShopifyApp\Util::useNativeAppBridge())
            <script src="https://unpkg.com/@shopify/app-bridge{{ \Osiset\ShopifyApp\Util::getShopifyConfig('appbridge_version') ? '@'.config('shopify-app.appbridge_version') : '' }}"></script>
            <script src="https://unpkg.com/@shopify/app-bridge-utils{{ \Osiset\ShopifyApp\Util::getShopifyConfig('appbridge_version') ? '@'.config('shopify-app.appbridge_version') : '' }}"></script>
            <script
                @if(\Osiset\ShopifyApp\Util::getShopifyConfig('turbo_enabled'))
                    data-turbolinks-eval="false"
                @endif
            >
                var AppBridge = window['app-bridge'];
                var actions = AppBridge.actions;
                var utils = window['app-bridge-utils'];
                var createApp = AppBridge.default;
                
                // var url = decodeURIComponent(window.location.href);
                // const params = new URLSearchParams(url);
                // const hostValue = params.get('host');
                var url = decodeURIComponent(window.location.href);

                const queryString = url.split('?')[1];

                // split query string into an array of parameter-value pairs
                const params = queryString.split('&');

                // loop through parameters to find the 'host' parameter and its value
                let hostValue;
                params.forEach(param => {
                const [key, value] = param.split('=');
                    if (key === 'host') {
                        hostValue = value;
                    }
                });

                if(typeof hostValue == 'undefined'){
                    var y = decodeURIComponent(window.location.href);
                    const x = new URLSearchParams(y);
                    hostValue = x.get('host');
                }

                var app = createApp({
                    apiKey: "{{ \Osiset\ShopifyApp\Util::getShopifyConfig('api_key', $shopDomain ?? Auth::user()->name ) }}",
                    shopOrigin: "{{ $shopDomain ?? Auth::user()->name }}",
                    host: hostValue,
                    forceRedirect: true,
                });

                var TitleBar = actions.TitleBar;
                var Button = actions.Button;
                var ButtonGroup = actions.ButtonGroup;
                var Loading = actions.Loading;
                var loading = Loading.create(app);
                var Redirect = actions.Redirect;
                var redirect = Redirect.create(app);
                var Toast = actions.Toast;
                var ResourcePicker = actions.ResourcePicker;
                var ChannelMenu = actions.ChannelMenu;
                var NavigationMenu = actions.NavigationMenu;
                var AppLink = actions.AppLink;
                var menuItems = [];
                @foreach(config('menu-front') as $menuItem)
                    var itemsLink = AppLink.create(app,{
                        label : '{{ $menuItem["label"] }}',
                        destination : '{{ $menuItem["destination"] }}'
                    });
                    menuItems.push(itemsLink);
                @endforeach
                const navigationMenu = NavigationMenu.create(app, {
                    items: menuItems,
                });

                breadcrumbs('','Dashboard');
               
                function breadcrumbs(label_page,page){
                    const breadcrumb = Button.create(app, { label: label_page });
                    const faq = Button.create(app, { label: 'FAQ' });
                    const plan = Button.create(app, { label: 'Plans' });
                    const addons = Button.create(app, { label: 'Add-ons' });
                    const bookaMeetingButton = Button.create(app, { label: 'Book A Meeting' });

                    var link="";
                    if(label_page == "Settings"){
                        var link = "/settings/config";
                    }else if(label_page == "Zone List"){
                        var link = "/zone/zones";
                    }else if(label_page == "Location List"){
                        var link = "/location/locations";
                    }else if(label_page == "Dashboard"){
                        var link = "/";
                    }else if(label_page == "Shipping Rate Settings"){
                        var link = "/shippingrate/shipping-profile";
                    }
                    const redirect = Redirect.create(app);

                    var appIntegration = Button.create(app, {
                        label: 'App Integration',
                        // disabled: RouteName == 'dashboard' ? true : false
                    });
                    var mainNotification = Button.create(app, {
                        label: 'Mail Notification',
                        // disabled: RouteName == 'orders' ? true : false
                    });
                    var packingSlip = Button.create(app, {
                        label: 'Packaging Slip',
                        // disabled: RouteName == 'email-listing' ? true : false
                    });

                    appIntegration.subscribe('click', () => {
                        loading.dispatch(Loading.Action.START);
                        app.dispatch(Redirect.toApp({
                            path: '/appintigration/intigration'
                        }));
                    });
                    mainNotification.subscribe('click', () => {
                        loading.dispatch(Loading.Action.START);
                        app.dispatch(Redirect.toApp({
                            path: '/appintigration/mail-notification'
                        })); 
                    });
                    packingSlip.subscribe('click', () => {
                        loading.dispatch(Loading.Action.START);
                        app.dispatch(Redirect.toApp({
                            path: '/appintigration/packaging-slip'
                        }));
                    });
                    addons.subscribe(Button.Action.CLICK, () => {
                        loading.dispatch(Loading.Action.START);
                        app.dispatch(Redirect.toApp({
                            path: '/add-ons'
                        }));
                    });
                    plan.subscribe(Button.Action.CLICK, () => {
                        loading.dispatch(Loading.Action.START);
                        app.dispatch(Redirect.toApp({
                        path: '/plans'
                        }));
                    });
                    breadcrumb.subscribe(Button.Action.CLICK, () => {
                        app.dispatch(Redirect.toApp({ 
                            path: link 
                        }));
                    });

                    var dashboardGroupButton = ButtonGroup.create(app, {
                        label: 'Integration',
                        buttons: [appIntegration, mainNotification, packingSlip]
                    });

                    bookaMeetingButton.subscribe(Button.Action.CLICK, () => {
                        redirect.dispatch(Redirect.Action.REMOTE, {
                            url: 'https://calendly.com/appjetty-support/30min?month=2023-03',
                            newContext: true,
                        });
                    });
                    faq.subscribe(Button.Action.CLICK, () => {
                        redirect.dispatch(Redirect.Action.REMOTE, {
                            url: 'https://appjettyshopifyapps.deskxpand.com/support/home',
                            newContext: true,
                        });
                    });
                        
                    const titleBarOptions = {
                        title: page,
                        breadcrumbs: breadcrumb,
                        buttons: {
                            primary: bookaMeetingButton,
                            secondary: [dashboardGroupButton,plan,addons,faq]
                            
                        },
                    };
                    const myTitleBar = TitleBar.create(app, titleBarOptions);
                }
            </script>
            @include('shopify-app::partials.token_handler')
            @include('shopify-app::partials.flash_messages')
        @endif
        @yield('scripts')
    </body>
</html>
