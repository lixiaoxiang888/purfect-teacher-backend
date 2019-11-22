<div class="sidebar-container">
    <div class="sidemenu-container navbar-collapse collapse fixed-menu">
        <div id="remove-scroll" class="left-sidemenu">
            <ul class="sidemenu  page-header-fixed sidemenu-closed" data-keep-expanded="false"
                data-auto-scroll="true" data-slide-speed="200" style="padding-top: 20px">
                <li class="sidebar-toggler-wrapper hide">
                    <div class="sidebar-toggler">
                        <span></span>
                    </div>
                </li>
                <li class="sidebar-user-panel">
                    <div class="user-panel">
                        <div class="pull-left image">
                            <img src="{{ asset('assets/img/dp.jpg') }}" class="img-circle user-img-circle"
                                 alt="User Image" />
                        </div>
                        <div class="pull-left info">
                            <p>超级管理员</p>
                            <a href="#"><i class="fa fa-circle user-online"></i><span class="txtOnline">
												Online</span></a>
                        </div>
                    </div>
                </li>

                <!--begin-->
                <li class="nav-item start">
                    <a href="{{ route('home') }}" class="nav-link nav-toggle">
                        <i class="material-icons">home</i>
                        <span class="title">切换学校</span>
                    </a>
                </li>
                @if(session('school.id'))
                <li class="nav-item {{ \App\Utils\Misc\Nav::IsBasicNav() ? 'active' : null }}">
                    <a href="{{ route('school_manager.school.view') }}" class="nav-link">
                        <i class="material-icons">business</i>
                        <span class="title">{{ session('school.name') }}</span>
                    </a>
                </li>

                    <li class="nav-item">
                        <a href="{{ route('school_manager.facility.list') }}" class="nav-link">
                            <i class="material-icons">dashboard</i>
                            <span class="title">设备管理</span>
                        </a>
                    </li>

@include('layouts.desktop.elements.courses_menu_group')
@include('layouts.desktop.elements.recruitment_menu_group')



                <li class="nav-item">
                    <a href="{{ route('school_manager.welcome.manager',['uuid'=>session('school.uuid')])  }}" class="nav-link">
                        <i class="material-icons">face</i>
                        <span class="title">迎新助手</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('teacher.conference.index',['uuid'=>session('school.uuid')])  }}" class="nav-link">
                        <i class="material-icons">event_seat</i>
                        <span class="title">会议管理</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>
