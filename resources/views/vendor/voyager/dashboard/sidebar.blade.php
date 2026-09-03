

<style>

    /* Allow multi-line menu text */
    .voyager .side-menu .navbar-nav li a .title:hover {
        white-space: normal !important;
        line-height: 16px;
        word-break: break-word;
        display: block;
    }

</style>

<div class="side-menu sidebar-inverse">
    <nav class="navbar navbar-default" role="navigation">
        <div class="side-menu-container">
            <div class="navbar-header">
                <a class="navbar-brand" href="{{ route('voyager.dashboard') }}">
                    <div class="logo-icon-container">
                        <?php $admin_logo_img = Voyager::setting('admin.icon_image', ''); ?>
                        @if($admin_logo_img == '')
                            <img src="{{ voyager_asset('images/logo-icon-light.png') }}" alt="Logo Icon">
                        @else
                            <img src="{{ Voyager::image($admin_logo_img) }}" alt="Logo Icon">
                        @endif
                    </div>
                    <div class="title">{{Voyager::setting('admin.title', 'VOYAGER')}}</div>
                </a>
            </div><!-- .navbar-header -->

            <div class="panel widget center bgimage"
                 style="background-image:url({{ Voyager::image( Voyager::setting('admin.bg_image'), voyager_asset('images/bg.jpg') ) }}); background-size: cover; background-position: 0px;">
                <div class="dimmer"></div>
                <div class="panel-content">
                    <img src="{{ $user_avatar }}" class="avatar" alt="{{ Auth::user()->name }} avatar">
                    <h4>{{ ucwords(Auth::user()->name) }}</h4>
                    <p>{{ Auth::user()->email }}</p>

                    <a href="{{ route('voyager.profile') }}"
                       class="btn btn-primary">{{ __('voyager::generic.profile') }}</a>
                    <div style="clear:both"></div>
                </div>
            </div>

        </div>
        <div id="adminmenu">
            {{--            <admin-menu :items="{{ menu('admin', '_json') }}"></admin-menu>--}}


            @php
                    $menuItems = menu('admin', '_json');

                     $filteredMenu = collect(json_decode($menuItems, true))->filter(function ($item) {
                        if( $item['id'] == 1){
                            return $item;
                        }elseif ((!isset($item['children']) || empty($item['children'])) && empty($item['route'])) {
                             return Auth::user()->hasPermission('browse_' . $item['route']);
                        }else{
                             if(in_array(\App\Helpers\CommonClass::user()->role_id,[101, 107, 108, 109]) && $item['title'] == 'Management'){  //Do not show management memu for agent, agency, company
                                 return [];
                             }

                             if(!in_array(\App\Helpers\CommonClass::user()->role_id,[1, 101]) && $item['title'] == 'Agent Panel'){  //only allowed for sadmin and agent
                                 return [];
                             }
                             if(!in_array(\App\Helpers\CommonClass::user()->role_id,[1, 107]) && $item['title'] == 'Company Panel'){  //only allowed for sadmin and company
                                 return [];
                             }
                             if(!in_array(\App\Helpers\CommonClass::user()->role_id,[1, 108]) && $item['title'] == 'Agency Panel'){  //only allowed for sadmin and agency
                                 return [];
                             }
                             if(!in_array(\App\Helpers\CommonClass::user()->role_id,[1, 109]) && $item['title'] == 'Sub Agent Panel'){  //only allowed for sadmin and agency
                                 return [];
                             }
                             return $item;

                        }

                        // Filter child items
                        $item['children'] = collect($item['children'])->filter(function ($child) {
                            return Auth::user()->hasPermission('browse_' . $child['route']);
                        })->values()->toArray();

                        return !empty($item['children']);


                    })->values()->toJson();
            @endphp
            <admin-menu :items="{{ $filteredMenu }}"></admin-menu>
        </div>
    </nav>
</div>


