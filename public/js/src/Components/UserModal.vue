<template>
    <div id="user-modal" v-if="currentUserId !== 0">
        <transition name="user-modal-fade">
            <div class="user-modal-backdrop" role="dialog">
                <div class="user-modal" ref="modal">
                    <header class="user-modal-header">
                        <slot name="header">
                            <h2>
                                InpUserDetails
                            </h2>

                            <button type="button" class="btn-close btn-right" @click="close()" aria-label="Close modal">
                                x
                            </button>
                        </slot>
                    </header>

                    <spinner v-if="!userLoaded"></spinner>
                    <section class="user-modal-body">
                        <p id="error" class="text-danger" v-if="this.error.length > 1"> {{ error }}</p>
                        <transition name="slide-down">
                        <slot name="body" v-if="userLoaded && user.id !== undefined">
                            <div id="user-contents">
                                <p><span class="label"> Id:</span> <span class="value" v-text="user.id"> </span> </p>
                                <p><span class="label">Name:</span> <span class="value" v-text="user.name"> </span> </p>
                                <p><span class="label">Username:</span> <span class="value" v-text="user.username"> </span> </p>
                                <p><span class="label">Email    :</span> <span  class="value"><a class="link" :href="'mailto:' + user.email" v-text="user.email"></a> </span> </p>
                                <p><span class="label">Address:</span></p>
                                <p><span class="label">&emsp;Street:</span>  <span  class="value" v-text="user.street"> </span></p>
                                <p><span class="label">&emsp;Suite:</span>  <span  class="value" v-text="user.suite"> </span></p>
                                <p><span class="label">&emsp;City:</span>  <span  class="value" v-text="user.city"> </span></p>
                                <p><span class="label">&emsp;Zipcode:</span>  <span  class="value" v-text="user.zipcode"> </span></p>
                                <p>&emsp;<span class="label">Geo:</span></p>
                                <p><span class="label">&emsp;&emsp;Lat:</span>  <span  class="value" v-text="user.lat"> </span></p>
                                <p><span class="label">&emsp;&emsp;Lng:</span>  <span  class="value" v-text="user.lng"> </span></p>
                                <p><span class="" style="font-size: .8rem;">&emsp;&emsp;<a class="link" :href="latLngLink(user)" target="_blank"> find on map</a></span></p>


                                <p><span class="label">Phone:</span> <span  class="value"><a class="link" :href="'tel:' + user.phone" v-text="user.phone"></a> </span> </p>
                                <p><span class="label">Website:</span> <span  class="value"> <a class="link" :href="'http://' + user.website" v-text="user.website" target="_blank"></a></span> </p>
                                <p><span class="label">Company:</span></p>
                                <p><span class="label">&emsp;Name:</span>  <span  class="value" v-text="user.companyName"> </span></p>
                                <p><span class="label">&emsp;Catch Phrase:</span>  <span  class="value" v-text="user.companyCatchPhrase"> </span></p>
                                <p><span class="label">&emsp;Bs:</span>  <span  class="value" v-text="user.companyBs"> </span></p>
                            </div>
                        </slot>
                        </transition>

                    </section>

                    <footer class="modal-footer">
                        <slot name="footer">
                            <button type="button" class="btn btn-green" @click="close()" aria-label="Close modal">
                                Close!
                            </button>
                        </slot>
                    </footer>
                </div>
            </div>
        </transition>
    </div>

</template>

<script>

    import axios from "axios";
    import Spinner from "./Spinner.vue";

    export default {
        name: 'user-modal',
        components: {
            Spinner,
        },
        props: ['userId', 'ajaxUrl', 'nonce', 'hook'],
        data() {
            return {
                error: '',
                display : true,
                currentUserId: this.userId,
                userLoaded: false,
                ID_REQUEST : 'id',
                user: '',
            }
        },

        methods: {
            close () {
                this.currentUserId = 0;
            },
            latLngLink(user) {
                return `https://maps.google.com/?q=${user.lat},${user.lng}`;
            },
            displayUser() {
                let formData = new FormData;
                formData.append('requestType', this.ID_REQUEST);
                formData.append('id', this.userId);
                this.request(formData)
                    .then(response => {
                        this.user = response.data.reply;
                    }).catch(error => {
                        console.log(error);
                        this.error = error.message;
                    }).finally( () => {
                        this.userLoaded = true;
                    });
            },
            request(formData) {
                formData.append('nonce', this.nonce);
                formData.append('action', this.hook);
                return axios.post(this.ajaxUrl, formData,{timeout: 4500});
            }
        },
        computed : {

        },
        mounted () {
        },
        created() {
            this.displayUser();
        },
    }
</script>

<style>

    .label{
        font-weight: 400;
        color:#ce93d8;
    }
    .value{
        color: #007bff;
    }
    .link {
        color : #0e2de5;
    }
    .modal-body p{
        margin: 0px;
    }

    p {
        margin: 0px;
    }
    .btn {
        padding: 8px 16px;
        border-radius: 3px;
        font-size: 14px;
        cursor: pointer;
    }

    .user-modal-backdrop {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0, 0, 0, 0.3);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .user-modal {
        /*margin-top: -350px;*/
        background: #ffffff;
        box-shadow: 2px 2px 20px 1px;
        overflow-x: auto;
        display: flex;
        flex-direction: column;
    }

    .user-modal-header,
    .user-modal-footer {
        padding: 15px;
        display: flex;
    }

    .user-modal-header {
        border-bottom: 1px solid #eeeeee;
        color: #4aae9b;
        justify-content: space-between;
    }

    .user-modal-footer {
        border-top: 1px solid #eeeeee;
        justify-content: flex-end;
    }

    .user-modal-body {
        position: relative;
        padding: 20px 40px;
    }

    .btn-close {
        border: none;
        font-size: 20px;
        padding: 20px;
        cursor: pointer;
        font-weight: bold;
        color: #4aae9b;
        background: transparent;
        color: red;
    }

    .btn {
        color: white;
        background: #4aae9b;
        border: 1px solid #4aae9b;
        border-radius: 2px;
    }

    .user-modal-fade-enter,
    .user-modal-fade-leave-active {
        opacity: 0;
    }

    .user-modal-fade-enter-active,
    .user-modal-fade-leave-active {
        transition: opacity 0.5s ease;
    }

    .slide-down-enter-active,
    .slide-down-leave-active {
        transition: max-height 0.5s ease-in-out;
    }

    .slide-down-enter-to,
    .slide-down-leave {
        overflow: hidden;
        max-height: 1000px;
    }

    .slide-down-enter,
    .slide-down-leave-to {
        overflow: hidden;
        max-height: 0;
    }
    a[target="_blank"]::after {
        content: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAAQElEQVR42qXKwQkAIAxDUUdxtO6/RBQkQZvSi8I/pL4BoGw/XPkh4XigPmsUgh0626AjRsgxHTkUThsG2T/sIlzdTsp52kSS1wAAAABJRU5ErkJggg==);
        margin: 0 3px 0 5px;
    }

</style>
