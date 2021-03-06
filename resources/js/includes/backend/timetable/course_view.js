// 查看某个课程的课程表的程序
import {Constants} from "../../../common/constants";
import {Util} from "../../../common/utils";
import {getTimeSlots} from "../../../common/timetables";

if(document.getElementById('school-timetable-course-viewer-app')){
    new Vue({
        el: '#school-timetable-course-viewer-app',
        data() {
            return {
                timetable: [],
                timeSlots: [],
                // 最后被选定的班级名称
                subTitle: '',
                reloading: false, // 只是课程表的预览数据是否整备加载中
                weekType: Constants.WEEK_NUMBER_ODD, // 默认是单周
                // 加载课程表所必须的项目
                schoolId: null,
                courseId: null,
                courseName: null,
                year: null,
                term: null,
            }
        },
        created() {
            this.schoolId = document.getElementById('timetable-current-school-id').dataset.school;
            this.courseId = document.getElementById('timetable-current-course-id').dataset.id;
            this.courseName = document.getElementById('timetable-current-course-name').dataset.name;
            this.year = document.getElementById('timetable-current-year').dataset.year;
            this.term = document.getElementById('timetable-current-term').dataset.term;
            for (let i = 0; i < 7; i++) {
                let rows = [];
                for (let j = 0; j < 8; j++) {
                    rows.push({});
                }
                this.timetable.push(rows);
            }

            // 把时间段数据取来, 然后去生成课程表左边第一栏
            getTimeSlots(this.schoolId).then(res => {
                if(Util.isAjaxResOk(res)){
                    this.timeSlots = res.data.data.time_frame;
                }
            })
        },
        mounted() {
            this.refreshTimetableHandler({
                course:{
                    name: this.courseName
                }
            });
        },
        methods: {
            // 刷新课程表数据
            refreshTimetableHandler: function(payload){
                // 把数据保存到缓存中
                if(!Util.isEmpty(payload.teacher)){
                    this.subTitle = payload.teacher.name;
                }

                if(!Util.isEmpty(payload.weekType)){
                    this.weekType = payload.weekType;
                }

                this.reloading = true;
                axios.post(
                    Constants.API.TIMETABLE.LOAD_TIMETABLE,
                    {
                        course: this.courseId,
                        year: this.year,
                        term: this.term,
                        school: this.schoolId,
                        weekType: this.weekType,
                    }
                ).then(res => {
                    if(Util.isAjaxResOk(res) && res.data.data.timetable !== ''){
                        // 表示加载到了有效的课程表
                        this.timetable = res.data.data.timetable;
                        this.$notify({
                            title: '成功',
                            message: this.subTitle + '的课程表加载完毕',
                            type: 'success',
                            position: 'bottom-right'
                        });
                    }else{
                        this.timetable = [];
                    }
                    this.reloading = false;
                }).catch(e=>{
                    this.reloading = false;
                })
            },
        }
    });
}