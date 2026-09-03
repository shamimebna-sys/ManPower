<nav class="navbar navbar-default navbar-fixed-top navbar-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button class="hamburger btn-link">
                <span class="hamburger-inner"></span>
            </button>

            @section('breadcrumbs')
            <ol class="breadcrumb hidden-xs">
                @php
                $segments = array_filter(explode('/', str_replace(route('voyager.dashboard'), '', Request::url())));
                $url = route('voyager.dashboard');
                @endphp
                @if(count($segments) == 0)
                    <li class="active"><i class="voyager-boat"></i> {{ __('voyager::generic.dashboard') }}</li>
                @else
                    <li class="active">
                        <a href="{{ route('voyager.dashboard')}}"><i class="voyager-boat"></i> {{ __('voyager::generic.dashboard') }}</a>
                    </li>
                    @foreach ($segments as $segment)
                        @php
                        $url .= '/'.$segment;
                        @endphp
                        @if ($loop->last)
                            <li>{{ ucfirst(urldecode($segment)) }}</li>
                        @else
                            <li>
                                <a href="{{ $url }}">{{ ucfirst(urldecode($segment)) }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            </ol>
            @show
        </div>



        <!-- Notification Icon -->
        <ul class="nav navbar-nav @if (__('voyager::generic.is_rtl') == 'true') navbar-left @else navbar-right @endif">

            <li class="nav-item position-relative" onclick="openModal('{{route("admin.ajax.notifications")}}', false)" >
                <a href="#" class="nav-link notification-container">
                    <i class="voyager-bell notification-icon"></i>
                    <span class="notification-badge animate-pulse" id="notification_count" style="display:none">0</span>
                </a>
            </li>

            <li class="dropdown profile">
                <a href="#" class="dropdown-toggle text-right" data-toggle="dropdown" role="button"
                   aria-expanded="false"><img src="{{ $user_avatar }}" class="profile-img"> <span
                            class="caret"></span></a>
                <ul class="dropdown-menu dropdown-menu-animated">
                    <li class="profile-img">
                        <img src="{{ $user_avatar }}" class="profile-img">
                        <div class="profile-body">
                            <h5>{{ Auth::user()->name }}</h5>
                            <h6>{{ Auth::user()->email }}</h6>
                        </div>
                    </li>
                    <li class="divider"></li>
                    <?php $nav_items = config('voyager.dashboard.navbar_items'); ?>
                    @if(is_array($nav_items) && !empty($nav_items))
                    @foreach($nav_items as $name => $item)
                    <li {!! isset($item['classes']) && !empty($item['classes']) ? 'class="'.$item['classes'].'"' : '' !!}>
                        @if(isset($item['route']) && $item['route'] == 'voyager.logout')
                        <form action="{{ route('voyager.logout') }}" method="POST">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger btn-block">
                                @if(isset($item['icon_class']) && !empty($item['icon_class']))
                                <i class="{!! $item['icon_class'] !!}"></i>
                                @endif
                                {{__($name)}}
                            </button>
                        </form>
                        @else
                        <a href="{{ isset($item['route']) && Route::has($item['route']) ? route($item['route']) : (isset($item['route']) ? $item['route'] : '#') }}" {!! isset($item['target_blank']) && $item['target_blank'] ? 'target="_blank"' : '' !!}>
                            @if(isset($item['icon_class']) && !empty($item['icon_class']))
                            <i class="{!! $item['icon_class'] !!}"></i>
                            @endif
                            {{__($name)}}
                        </a>
                        @endif
                    </li>
                    @endforeach
                    @endif
                </ul>
            </li>
        </ul>


    </div>
</nav>



<!-- Modal notifications -->
<div id="notificationModal" class="modal modal-info fade" tabindex="-1">
    <div class="modal-dialog" id="modal-size">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="modal-title">Title</h4>
            </div>
            <div class="modal-body" id="notificationModalBody"></div>

            {{--<div class="modal-footer">
                &nbsp; <button type="button" class="btn btn-dark pull-right" data-dismiss="modal">Close</button>&nbsp;
            </div>--}}
        </div>
    </div>
</div>

<style>
    #myModal .modal-body{
        color: black;
    }

</style>

<script>
    function openModal(url, loader=false, modal_size='modal-md', title='Notifications'){

        if ($('#modal-size').hasClass('modal-sm')) {
            $('#modal-size').removeClass('modal-sm');
        }
        if ($('#modal-size').hasClass('modal-md')) {
            $('#modal-size').removeClass('modal-md');
        }
        if ($('#modal-size').hasClass('modal-lg')) {
            $('#modal-size').removeClass('modal-lg');
        }
        $('#modal-size').addClass(modal_size);
        $('#modal-title').text(title);


        $.ajax({
            type:'get',
            url:url,
            data: { "_token": $('meta[name="csrf-token"]').attr('content') },
            beforeSend: function() {
                $('#notificationModalBody').html('');
                if(loader){
                    $("#voyager-loader").show();
                }

            },
            success:function(data) {
                if(loader){
                    $("#voyager-loader").hide();
                }
                $('#notificationModalBody').html(data);
                $("#notificationModal").modal('show');
            },error:function(err){
                $("#notificationModal").modal('hide');
                if(loader){
                    $("#voyager-loader").hide();
                }
                toastr.warning("Something went wrong.");
            }
        });

    }

    function notificationCounter() {
        fetch('{{route("admin.ajax.notification-counter")}}') // Replace with your URL
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json(); // or response.text(), etc.
            })
            .then(data => {
                document.getElementById('notification_count').style.display = 'none';
                if(data.count > 0){
                    document.getElementById('notification_count').style.display = 'block';
                    document.getElementById('notification_count').textContent =data.count;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
    }

    notificationCounter();
    setInterval(notificationCounter, 60000);

</script>
