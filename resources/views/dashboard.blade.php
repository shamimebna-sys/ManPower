@extends('layout')

@section('title', 'Dashboard')

@section('header')
    @livewireStyles
@stop



@section('content')

    <!-- Dashboard Content Section Start -->
    <div class="dashboard-content-section section bg_color--5">
        <div class="container-fluid p-0">
            <div class="row g-0">
                <div class="col-xl-2 col-lg-3">
                    <div class="dashboard-sidebar">
                        <div class="dashboard-menu">
                            <ul class="nav">
                                <li>
                                    <h3>Main</h3>
                                    <ul>
                                        <li><a class="active" href="dashboard.html"><i class="lnr lnr-chart-bars"></i> Dashboard </a></li>
                                        <li><a href="message.html"><i class="lnr lnr-bubble"></i> Messages </a></li>
                                        <li><a href="job-alerts.html"><i class="lnr lnr-envelope"></i> Job Alerts </a></li>
                                        <li><a href="reviews.html"><i class="lnr lnr-star"></i> Approval </a></li>
                                    </ul>
                                </li>
                                <li>
                                    <h3>Approval</h3>
                                    <ul>
                                        <li><a href="message.html"><i class="lnr lnr-user"></i> Candidates </a></li>
                                        <li><a href="applications.html"><i class="lnr lnr-briefcase"></i> Applications </a></li>
                                        <li><a href="bookmarks.html"><i class="lnr lnr-bookmark"></i> Bookmarks </a></li>
                                        <li><a href="follows.html"><i class="lnr lnr-pointer-right"></i> Follows </a></li>
                                    </ul>
                                </li>
                                <li>
                                    <h3>Account</h3>
                                    <ul>
                                        <li><a href="profile.html"><i class="lnr lnr-user"></i> Profile </a></li>
                                        <li><a href="orders.html"><i class="lnr lnr-cart"></i> Refile balance </a></li>
                                        <li><a href="{{route('web.logout')}}"><i class="lnr lnr-exit-up"></i> Logout </a></li>
                                    </ul>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>


                <div class="col-xl-10 col-lg-9">
                    <div class="dashboard-main-inner">
                        <div class="row">
                            <div class="col-12">
                                <div class="page-breadcrumb-content mb-40">
                                    <h1>Dashboard</h1>
                                </div>
                            </div>
                        </div>
                        <div class="dashboard-overview">
                            <div class="row">
                                <div class="col-xl-8 col-12">
                                    <div class="submited-applications mb-50">
                                        <div class="applications-heading">
                                            <h3>Already Applied</h3>
                                        </div>
                                        <div class="applications-main-block">
                                            <div class="applications-table">
                                                <table class="table">
                                                    <thead>
                                                    <tr>
                                                        <th class="width-35">Applied Job</th>
                                                        <th class="width-15">Employer</th>
                                                        <th class="width-12">Status</th>
                                                        <th class="width-15">Applied Date</th>
                                                        <th class="width-23 text-right">Action</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr class="application-item">
                                                        <td class="application-job">
                                                            <h3><a href="#">tester</a></h3>
                                                        </td>

                                                        <td class="application-employer">
                                                            <a class="dotted" href="#">Unknown</a>
                                                        </td>

                                                        <td class="status">
                                                            <span class="pending">Pending</span>
                                                        </td>

                                                        <td class="application-created">
                                                            <span> May 19, 2022 </span>
                                                        </td>

                                                        <td class="view-application text-xl-right">
                                                            <a href="#" class="view-application">View Application</a>
                                                        </td>
                                                    </tr>
                                                    <tr class="application-item">
                                                        <td class="application-job">
                                                            <h3><a href="#">Android & IOS Developer</a></h3>
                                                        </td>

                                                        <td class="application-employer">
                                                            <a class="dotted" href="#">Inwave Studio</a>
                                                        </td>

                                                        <td class="status">
                                                            <span class="pending">Pending</span>
                                                        </td>

                                                        <td class="application-created">
                                                            <span> May 3, 2022 </span>
                                                        </td>

                                                        <td class="view-application text-xl-right">
                                                            <a href="#" class="view-application">View Application</a>
                                                        </td>
                                                    </tr>
                                                    <tr class="application-item">
                                                        <td class="application-job">
                                                            <h3><a href="#">Tax Manager</a></h3>
                                                        </td>

                                                        <td class="application-employer">
                                                            <a class="dotted" href="#">Vsmarttech</a>
                                                        </td>

                                                        <td class="status">
                                                            <span class="pending">Pending</span>
                                                        </td>

                                                        <td class="application-created">
                                                            <span> Apr 27, 2022 </span>
                                                        </td>

                                                        <td class="view-application text-xl-right">
                                                            <a href="#" class="view-application">View Application</a>
                                                        </td>
                                                    </tr>
                                                    <tr class="application-item">
                                                        <td class="application-job">
                                                            <h3><a href="#">IOS & Android Developer</a></h3>
                                                        </td>

                                                        <td class="application-employer">
                                                            <a class="dotted" href="#">Radio Game</a>
                                                        </td>

                                                        <td class="status">
                                                            <span class="pending">Pending</span>
                                                        </td>

                                                        <td class="application-created">
                                                            <span> Apr 19, 2022 </span>
                                                        </td>

                                                        <td class="view-application text-xl-right">
                                                            <a href="#" class="view-application">View Application</a>
                                                        </td>
                                                    </tr>
                                                    <tr class="application-item">
                                                        <td class="application-job">
                                                            <h3><a href="#">Android & IOS Developer</a></h3>
                                                        </td>

                                                        <td class="application-employer">
                                                            <a class="dotted" href="#">HasTech</a>
                                                        </td>

                                                        <td class="status">
                                                            <span class="pending">Pending</span>
                                                        </td>

                                                        <td class="application-created">
                                                            <span> May 19, 2022 </span>
                                                        </td>

                                                        <td class="view-application text-xl-right">
                                                            <a href="#" class="view-application">View Application</a>
                                                        </td>
                                                    </tr>
                                                    <tr class="application-item">
                                                        <td class="application-job">
                                                            <h3><a href="#">Construction Worker</a></h3>
                                                        </td>

                                                        <td class="application-employer">
                                                            <a class="dotted" href="#">Digital Vine</a>
                                                        </td>

                                                        <td class="status">
                                                            <span class="rejected">Rejected</span>
                                                        </td>

                                                        <td class="application-created">
                                                            <span> Dec 4, 2022 </span>
                                                        </td>

                                                        <td class="view-application text-xl-right">
                                                            <a href="#" class="view-application">View Application</a>
                                                        </td>
                                                    </tr>
                                                    <tr class="application-item">
                                                        <td class="application-job">
                                                            <h3><a href="#">Jr. Developer Shopify</a></h3>
                                                        </td>

                                                        <td class="application-employer">
                                                            <a class="dotted" href="#">HasThemes</a>
                                                        </td>

                                                        <td class="status">
                                                            <span class="rejected">Rejected</span>
                                                        </td>

                                                        <td class="application-created">
                                                            <span> May 19, 2022 </span>
                                                        </td>

                                                        <td class="view-application text-xl-right">
                                                            <a href="#" class="view-application">View Application</a>
                                                        </td>
                                                    </tr>
                                                    <tr class="application-item">
                                                        <td class="application-job">
                                                            <h3><a href="#">Receptionist</a></h3>
                                                        </td>

                                                        <td class="application-employer">
                                                            <a class="dotted" href="#">Digital Vine</a>
                                                        </td>

                                                        <td class="status">
                                                            <span class="approved">Approved</span>
                                                        </td>

                                                        <td class="application-created">
                                                            <span> Dec 4, 2022 </span>
                                                        </td>

                                                        <td class="view-application text-xl-right">
                                                            <a href="#" class="view-application">View Application</a>
                                                        </td>
                                                    </tr>
                                                    <tr class="application-item">
                                                        <td class="application-job">
                                                            <h3><a href="#">Recreation & Fitness Worker</a></h3>
                                                        </td>

                                                        <td class="application-employer">
                                                            <a class="dotted" href="#">Digital Asset</a>
                                                        </td>

                                                        <td class="status">
                                                            <span class="approved">Approved</span>
                                                        </td>

                                                        <td class="application-created">
                                                            <span> Dec 4, 2022 </span>
                                                        </td>

                                                        <td class="view-application text-xl-right">
                                                            <a href="#" class="view-application">View Application</a>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="application-pagination mb-30">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <ul class="page-pagination justify-content-center">
                                                            <li><a href="#"><i class="fa fa-angle-left"></i></a></li>
                                                            <li class="active"><a href="#">1</a></li>
                                                            <li><a href="#">2</a></li>
                                                            <li><a href="#"><i class="fa fa-angle-right"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4 col-12">
                                    <div class="notifications-applications mb-20 mb-sm-80 mb-xs-80">
                                        <div class="notifications-heading">
                                            <h3>Notifications</h3>
                                        </div>
                                        <div class="notifications-main-block">
                                            <div class="notification-listing">
                                                <div class="empty">
                                                    <h3>There are no notifications</h3>
                                                    <p>Your latest notifications will be displayed here</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Dashboard Content Section End -->


@stop



@section('footer')
    @livewireScripts
@stop
