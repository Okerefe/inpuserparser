<template>
    <div style="overflow-x: auto;">
        <div id="details" class="modal fade in" style="color:black;"data-keyboard="false" data-backdrop="static" role="dialog"><!--Edit Description popover-->
            <div class="modal-dialog">
                <div class="modal-content" id="location-comment-modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">InpUserDetails:</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="spinner-cover" id="modal-spinner-cover" style="display:none;">
                            <div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                        </div>
                        <p id="modal-error" class="text-danger" style="display: none;"></p>
                        <div id="user-contents" style="display: none;">
                            <p><span class="label"> Id:</span> <span class="value" id="id"> 4 </span> </p>
                            <p><span class="label">Name:</span> <span class="value" id="name"></span> </p>
                            <p><span class="label">Username:</span> <span class="value" id="username"></span> </p>
                            <p><span class="label">Email    :</span> <span class="value" id="email"></span> </p>
                            <p><span class="label">Address:</span></p>
                            <p><span class="label">&emsp;Street:</span>  <span class="value" id="street"></span></p>
                            <p><span class="label">&emsp;Suite:</span>  <span class="value" id="suite"></span></p>
                            <p><span class="label">&emsp;City:</span>  <span class="value" id="city"></span></p>
                            <p><span class="label">&emsp;Zipcode:</span>  <span class="value" id="zipcode"></span></p>
                            <p>&emsp;<span class="label">Geo:</span></p>
                            <p><span class="label">&emsp;&emsp;Lat:</span>  <span class="value" id="lat"></span></p>
                            <p><span class="label">&emsp;&emsp;Lng:</span>  <span class="value" id="lng"></span></p>

                            <p><span class="label">Phone:</span> <span class="value" id="phone"></span> </p>
                            <p><span class="label">Website:</span> <span class="value" id="website"></span> </p>
                            <p><span class="label">Company:</span></p>
                            <p><span class="label">&emsp;Name:</span>  <span class="value" id="companyName"></span></p>
                            <p><span class="label">&emsp;Catch Phrase:</span>  <span class="value" id="companyCatchPhrase"></span></p>
                            <p><span class="label">&emsp;Bs:</span>  <span class="value" id="companyBs"></span></p>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>

                </div><!-- modal-content-->
            </div><!-- modal-dialog -->
        </div><!-- modal fade for pictures form-->
        <p id="error" class="text-danger" v-if="this.error.length > 1"> {{ error }}</p>

        <spinner v-if="showSpinner"/>

        <div id="table-cover" v-if="!showSpinner" :key="tableKey + 'cover'">
            <table class="table table-hover" id="table">
                <thead>
                <tr>
                    <th scope="col" v-for="(column, i) in columns" :key="i">
                        {{ ucFirst(column) }}
                    </th>

                </tr>
                </thead>
                <tbody>

                <tr v-for="(user, i) in users" :key="i">
                    <td v-for="(column, i) in columns" :key="i"><a :id="'user-' + user.id" href="" v-on:click.prevent="showDetail(user.id)">
                         {{ user[column] }}
                    </a></td>
                </tr>

                </tbody>
            </table>
            <p v-if="searchError.length > 1" v-text="searchError"></p>
        </div>
        <user-modal
            v-if="currentUserId !== 0"
            :user-id="currentUserId"
            :key="currentUserIdKey"
            :ajax-url="this.ajaxUrl"
            :hook = "this.hook"
            :nonce="this.nonce">

        </user-modal>

    </div>
</template>

<script>

    import axios from "axios";
    import UserModal from "./UserModal.vue";
    import Spinner from "./Spinner.vue";

    export default {
        name: 'users-table',

        props: ['nonce', 'ajaxUrl', 'search', 'hook'],

        components : {
            Spinner,
            UserModal,
        },

        data() {
            return {
                tableKey : 1,
                showSpinner: 0,
                ALL_REQUEST: 'all',
                ID_REQUEST: 'id',
                SEARCH_REQUEST: 'search',
                users:  'stuff',
                columns: '',
                error: '',
                showModal: true,
                currentUserId : 0,
                currentUserIdKey : 0,
                searchError : '',
            }
        },

        methods: {

            ucFirst(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            },
            showDetail(id) {
                this.currentUserId = id;
                this.currentUserIdKey ++;
            },

            loadSearchedUsers() {
                let formData = new FormData;
                formData.append('requestType', this.SEARCH_REQUEST);
                formData.append('searchStr', this.search.str);
                formData.append('column', this.search.col);
                this.showSpinner = true;
                this.request(formData)
                    .then(response => {
                        if(response.data.reply.searchSuccess) {
                            this.columns = response.data.reply.columns;
                            this.users = response.data.reply.users;
                        } else {
                            this.error = response.data.reply.error;
                        }
                        this.tableKey++;
                    }).catch(error => {
                        this.error = error.message;
                    }).finally( () => {
                        this.showSpinner = false;
                    });
            },
            loadAllUsers() {
                let formData = new FormData;
                formData.append('requestType', this.ALL_REQUEST);
                this.showSpinner = true;
                this.request(formData)
                .then(response => {
                    this.users = response.data.reply.users;
                    this.columns = response.data.reply.columns;
                    this.tableKey++;
                }).catch(error => {
                    this.error = error.message;
                }).finally( () => {
                    this.showSpinner = false;
                });
            },

            request(formData) {
              formData.append('nonce', this.nonce);
              formData.append('action', this.hook);
              return axios.post(this.ajaxUrl, formData,{timeout: 4500});
            },
        },
        computed: {},

        created() {
            if(this.search.str.length > 2 && this.search.col !== '') {
                this.loadSearchedUsers();
            } else {
                this.loadAllUsers();
            }
        },
    }
</script>

<style>
    thead {
        color: #ce93d8;
    }
</style>