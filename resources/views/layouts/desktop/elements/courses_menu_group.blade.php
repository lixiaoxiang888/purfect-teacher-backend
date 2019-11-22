<li class="nav-item">
    <a href="javascript:void(0);" class="nav-link nav-toggle">
        <i class="material-icons">school</i>
        <span class="title">课程</span>
        <span class="arrow open"></span>
    </a>
    <ul class="sub-menu">
        <li class="nav-item">
            <a href="{{ route('school_manager.courses.manager',['uuid'=>session('school.uuid')]) }}" class="nav-link ">
                <span class="title">课程管理</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('teacher.textbook.manager') }}" class="nav-link ">
                <span class="title">教材管理</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('school_manager.timetable.manager.preview',['uuid'=>session('school.uuid')]) }}" class="nav-link">
                <span class="title">课程表管理</span>
            </a>
        </li>
    </ul>
</li>