@extends('layouts.app')
@section('content')
    <div class="col-sm-12 col-md-12 col-xl-12">
        <div class="card">
            <div class="card-head">
                <header>评学列表</header>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="row table-padding">
                        <div class="col-md-6 col-sm-6 col-6">
                            <a href="{{ route('school_manager.evaluate.student.add') }}" class="btn btn-primary">
                                创建 <i class="fa fa-plus"></i>
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table
                            class="table table-striped table-bordered table-hover table-checkable order-column valign-middle">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>标题</th>
                                <th>状态</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                                <tbody>
                                    @foreach($data as $key => $val)
                                        <tr>
                                            <td>{{ $key +1 }}</td>
                                            <td>{{ $val->title }}</td>
                                            <td>{{ $val->statusText() }}</td>
                                            <td>{{ $val->created_at }}</td>
                                            <td class="text-center">
                                            {{ \App\Utils\UI\Anchor::Print(['text'=>'编辑','class'=>'btn-edit-evaluate','href'=>route('school_manager.evaluate.student.edit',['id'=>$val->id])], \App\Utils\UI\Button::TYPE_DEFAULT,'edit') }}
                                            {{ \App\Utils\UI\Anchor::Print(['text'=>'删除','class'=>'btn-delete-evaluate btn-need-confirm','href'=>route('school_manager.evaluate.student.delete',['id'=>$val->id])], \App\Utils\UI\Button::TYPE_DANGER,'trash') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                        </table>
                    </div>
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
