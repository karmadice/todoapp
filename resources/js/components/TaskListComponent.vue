<template>
    <div class="card">
        <div class="card-body">
            <h2 class="title">My task list</h2>
            <hr>
            <div class="form-row">
                <div class="col-10">
                    <input class="form-control" type="text" placeholder="New task" v-on:keyup.enter="createTask()" v-model="task.body">
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-primary" @click="createTask()">Add Task</button>
                </div>
            </div>
            <ul class="nav nav-pills nav-fill mb-3 justify-content-center pt-3">
                <li class="nav-item">
                    <a class="tab-link" :class="{'active':isActive('current')}" v-on:click.prevent="fetchTasks()" href="#"><h3>Active tasks</h3></a>
                </li>
                <li class="nav-item">
                    <a class="tab-link" :class="{'active':isActive('archive')}" v-on:click.prevent="fetchTasks(1)" id="archive-task-tab" href="#"><h3>Archived tasks</h3></a>
                </li>
            </ul>
            <div class="tab-content" v-for="task in list">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex">
                    <span class="icon">
                           <i class="far" :class="{'fa-square': !task.archive,check: !task.archive, 'fa-check-square': task.archive, done: task.archive}" aria-hidden="true" v-on:click.prevent="archiveTask(task.id)"></i>
                        </span>
                        <p class="task">
                            {{ task.body }}
                            <small v-if="!task.archive && task.due_date !== null" :class="{'text-danger' : isOverDue(task.due_date)}">{{ prettyTime(task.due_date) }}</small>
                            <small v-if="task.archive && task.due_date !== null" :class="{'text-success' : isOverDue(task.due_date)}">{{ prettyTime(task.due_date) }}</small>
                        </p>
                        <span v-if="!task.archive" class="ml-auto p-2 icon"><i class="fas fa-pencil-alt" data-toggle="modal" data-target="#editTask" v-on:click.prevent="openModel(task.id)"></i></span>
                        <span v-if="task.archive" class="ml-auto p-2 icon"><i class="fa fa-trash" v-on:click.prevent="deleteTask(task.id)"></i></span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="modal fade" id="editTask" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modelTitleId">Edit task</h4>
                        <button type="button" class="close" v-on:click.prevent="deleteTask(currentTask.id)" data-dismiss="modal" aria-label="Close">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                    <div class="modal-body" v-if="currentTask.task.length !== 0">
                        <div class="form-group">
                            <input class="form-control" v-autofocus type="text" placeholder="New task" v-model="currentTask.task.body">
                        </div>
                        <div class="form-group" id="date_picker">
                            <Datepicker placeholder="Due date" name="date" v-model="currentTask.task.due_date" :config="options"></Datepicker>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" v-on:click="endEditing(currentTask.task)">Save</button>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</template>

<script>
    import jQuery from 'jquery';
    import moment from 'moment';
    import Datepicker from 'vue-bootstrap-datetimepicker';
    jQuery.extend(true, jQuery.fn.datetimepicker.defaults,{
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'fas fa-calendar-check',
            clear: 'far fa-trash-alt',
            close: 'far fa-times-circle'
        }
    });
    export default {
        directives: {
            'autofocus': {
                inserted(el) {
                    el.focus();
                }
            }
        },

        data() {
            return {
                message: 'Double click for editing.',
                list: [],
                timer: '',
                task: {
                    id: '',
                    body: '',
                    due_date: '',
                    archive: ''
                },
                currentTask:{
                    id:'',
                    task:{}
                },
                validation_error:{
                    error: '',
                    error_status: ''
                },
                editingTask: {},
                activeItem: 'current',
                options: {
                    // https://momentjs.com/docs/#/displaying/
                    format: 'YYYY-MM-DD H:mm:ss',
                    minDate: moment(),
                    //enabledHours: this.calculateRemainingHours(),
                    useCurrent: true,
                    showClear: true
                }
            }
        },

        created() {
            this.fetchTasks();
            // Refresh task list every min
            this.timer = setInterval(this.fetchTasks, 60000)
        },

        // Clear timer when page reloaded
        beforeDestroy() {
            clearInterval(this.timer)
        },

        methods: {
            openModel(taskId){
                this.currentTask.id = taskId;
                this.fetchTask(taskId);
            },

            // Change time stamps to human readable format 
            prettyTime(time){
                return moment(time).fromNow();
            },

            // determine if task is over due
            isOverDue(time){
                return moment().isAfter(time);
            },
            
            // Get all tasks from database
            fetchTasks(archive = null) {

                if (archive === null) {
                    var url = 'current_tasks';
                    this.setActive('current');
                } else {
                    var url = 'archived_tasks';
                    this.setActive('archive');
                }

                axios.get(url).then(result => {
                    this.list = result.data
                });
            },

            // Get particular task from database
            fetchTask(taskId){
                axios.get('get_task/'+taskId).then(result => {
                    this.currentTask.task = result.data
                    console.log('current task ' + JSON.stringify(this.currentTask.task));
                });
            },

            // Check active menu item (current / archive task)
            isActive(menuItem) {
                return this.activeItem === menuItem;
            },

            // Set active menu item
            setActive(menuItem) {
                this.activeItem = menuItem;
            },

            // Create new task
            createTask() {
                if( !this.task.body.trim() ) {
                    return;
                }
                axios.post('create_task', this.task).then(result => {
                    this.task.body = '';
                    console.log('length: ', this.validation_error.length);
                    this.fetchTasks();
                }).catch(err => {
                    console.log(JSON.stringify(err.response.data.errors.body));
                });
            },

            editTask(task) {
                this.editingTask = task;
            },

            endEditing(task) {
                this.editingTask = {};
                if (!task.body.trim()) {
                    return;
                }
                axios.post('edit_task', task).then(result => {
                    $('#editTask').modal('hide');
                    this.fetchTasks();
                }).catch(err => {
                    console.log(err);
                });

            },

            deleteTask(id) {
                axios.post('delete_task/' + id).then(result => {
                    this.fetchTasks();
                }).catch(err => {
                    console.log(err);
                });
            },

            archiveTask(id) {
                axios.post('archive_task/' + id).then(result => {
                    this.fetchTasks();
                }).catch(err => {
                    console.log(err);
                });
            }
        },
        components: {
            Datepicker
        }
    }
</script>
