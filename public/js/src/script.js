import Vue from 'vue';

import Search from './Components/Search.vue';
import UsersTable from './Components/UsersTable.vue';
import UserModal from './Components/UserModal.vue';

const app = new Vue({
    el: '#container',
    components: {
        'search': Search,
        'users-table': UsersTable,
        'user-modal': UserModal,
    },

    data() {
        return {
            search : {
                str : '',
                col : '',
            },
            searchKey : 0
        }
    },
    watch: {
    },
    methods: {
        updateSearchStr(search) {
            this.search.str = search.str;
            this.search.col = search.col;
            this.searchKey++;
        },
    },
});
