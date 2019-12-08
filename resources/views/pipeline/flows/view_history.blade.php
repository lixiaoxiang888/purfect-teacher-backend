@php
    use App\Utils\UI\Anchor;
    use App\Utils\UI\Button;
@endphp
@extends('layouts.app')
@section('content')
    <div class="row" id="pipeline-flow-view-history-app">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <div class="card">
                <div class="card-head">
                    <header>审核 <i class="el-icon-loading" v-if="isLoading"></i> </header>
                </div>
                <div class="card-body">
                    <el-form ref="currentActionForm" :model="action" label-width="120px" style="padding: 10px;">
                        <el-form-item label="审核意见">
                            <el-select v-model="action.result" placeholder="请选择您的审核意见">
                                <el-option v-for="(re, idx) in results" :key="idx" :label="re.label" :value="re.id"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="原因说明">
                            <el-input type="textarea" placeholder="必填: 请写明原因" rows="6" v-model="action.content"></el-input>
                        </el-form-item>
                        <el-form-item label="选择附件">
                            <el-button type="primary" icon="el-icon-document" v-on:click="showFileManagerFlag=true">选择附件</el-button>
                            <ul style="padding-left: 0;">
                                <li v-for="(a, idx) in action.attachments" :key="idx">
                                    <p style="margin-bottom: 0;">
                                        <span>@{{ a.file_name }}</span>&nbsp;<el-button v-on:click="dropAttachment(idx, a)" type="text" style="color: red">删除</el-button>
                                    </p>
                                </li>
                            </ul>
                        </el-form-item>

                        <el-form-item>
                            <el-button type="primary" @click="onCreateActionSubmit">{{ trans('general.submit') }}</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-12 col-sm-12 col-12">
            <div class="card">
                <div class="card-head">
                    <header>详细流程 <i class="el-icon-loading" v-if="isLoading"></i></header>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <el-timeline :reverse="false">
                                <el-timeline-item
                                        v-for="(act, index) in history"
                                        :key="index"
                                        :timestamp="act.updated_at">
                                    <el-card>
                                        <h4 :class="resultTextClass(act.result)">
                                            @{{  resultText(act.result) }}
                                        </h4>
                                        <p>@{{ act.content }}</p>
                                    </el-card>
                                </el-timeline-item>
                            </el-timeline>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include(
                'reusable_elements.section.file_manager_component',
                ['pickFileHandler'=>'pickFileHandler']
            )
    </div>

    <div id="app-init-data-holder"
         data-school="{{ session('school.id') }}"
         data-actionid="{{ $actionId }}"
         data-flowid="{{ $userFlowId }}"
         data-useruuid="{{ \Illuminate\Support\Facades\Auth::user()->uuid }}"
    ></div>
@endsection