if(document.getElementById('school-teacher-management-rooms-app')){
    new Vue({
        el:'#school-teacher-management-rooms-app',
        data(){
            return {
                rooms: [],
                selectedDevice: {
                    building:{},
                    room:{}
                },
                showDetailFlag: false,
                apiToken: null,
            }
        },
        created(){
            const dom = document.getElementById('app-init-data-holder');
            this.schoolId = dom.dataset.school;
            this.apiToken = dom.dataset.token;
            this.rooms = JSON.parse(dom.dataset.rooms);
        },
        methods: {
            showDetail: function (row) {
                this.showDetailFlag = true;
                this.selectedDevice = row;
            },
            back: function(){
                window.location.href = '/h5/teacher/management/view?api_token=' + this.apiToken + '&type=employee';
            }
        }
    })
}