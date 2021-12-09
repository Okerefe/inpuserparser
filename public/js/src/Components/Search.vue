<template>
    <div class="row" style="padding: 2px;">
        <div class="md-form active-purple active-purple-2 mb-3 mt-0 col-8" style="margin: 0px !important; padding:0px;border-style: none;">
            <input
                class="form-control"
                type="text"
                placeholder="Type your Search"
                aria-label="search"
                v-model="searchStr"
                @input="updateSearchStr()">
        </div>

        <div class="col-2" style="margin:0px;padding:0px;border-style:none;">
            <input type="text"
                   :value="searchByText"
                   class="form-control searchByText"
                   disabled/>

        </div>

        <select class="browser-default custom-select form-control col-2 searchFields" v-model="searchColumn" @change="updateSearchStr()">
            <option v-for="field in fields" :value="field">{{ field }}</option>
        </select>
        <p id="search-info" class="text-info text-box" style="" v-html="searchInfo" v-if="searchStr.length > 0"></p>
    </div>

</template>

<script>

    export default {
        name: 'search',

        props:  ['searchByText', 'fields'],

        data() {
            return {
                searchStr: '',
                searchColumn: ''
            }
        },

        methods: {
            updateSearchStr(){

                // This Condition makes sure that the Event is Broadcasted
                // Only when the Column is Set and the searchStr length is > 3
                // It also allows for broadcast when the str length is 0 so to reset the page
                // The only downside here is that table reloads anytime the column is changed
                // Weather or not the searchStr is empty
                if(this.searchColumn !== '' && (this.searchStr.length > 2 || this.searchStr === '')) {
                    this.$emit('searchstr', {str: this.searchStr, col : this.searchColumn} )
                }

            }

        },
        computed : {
            searchInfo() {
                let str = '<ul>';
                if(this.searchStr.length < 3) {
                    str+= `<li>At least 3 characters: ${3 - this.searchStr.length} more</li>`
                }
                if(this.searchColumn === '') {
                    str+= `<li>Select a Column from 'Search By: '</li>`;
                }
                str += '</ul>';
                return str;
            }
        },

        created() {
        },
    }
</script>

<style>
    .text-block {
        white-space: pre;
        word-wrap: break-word;
    }

    .active-purple-2 input.form-control[type=text]:focus:not([readonly]) {
        border-bottom: 1px solid #ce93d8;
        box-shadow: 0 1px 0 0 #ce93d8;
    }
    .active-purple-2 input[type=text]:focus:not([readonly]) {
        border-bottom: 1px solid #ce93d8;
        box-shadow: 0 1px 0 0 #ce93d8;
    }
    .active-purple input.form-control[type=text] {
        border-bottom: 1px solid #ce93d8;
        box-shadow: 0 1px 0 0 #ce93d8;
    }
    .active-purple input[type=text] {
        border-bottom: 1px solid #ce93d8;
        box-shadow: 0 1px 0 0 #ce93d8;
    }
</style>