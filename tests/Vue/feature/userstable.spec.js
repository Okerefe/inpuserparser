/**
 * @jest-environment jsdom
 */
import { mount } from '@vue/test-utils'
import UsersTable from '../../../public/js/src/Components/UsersTable.vue'
import moxios from 'moxios'
import {afterEach, beforeEach} from "@jest/globals";

describe('UsersTable Component:', () => {
    beforeEach(function () {
        // import and pass your custom axios instance to this method
        moxios.install()
    })

    afterEach(function () {
        // import and pass your custom axios instance to this method
        moxios.uninstall()
    })

    function stubSuccessfulRequest() {
        moxios.stubRequest('http://somelink.com', {
            status: 200,
            response: {
                success: 'true',
                reply: {
                    users: [
                        {id : 1, name: "John", username: 'joedoe', email: 'joe@joe.com'},
                        {id : 2, name: "Jane", username: 'joejane', email: 'joe@jane.com'},
                    ],
                    columns: ['id', 'name', 'username', 'email'],
                }
            }
        });

    }

    function stubSearchRequest() {
        moxios.stubRequest('http://somelink.com', {
            status: 200,
            response: {
                success: 'true',
                reply: {
                    users: [
                        {id : 1, name: "Leane", username: 'Bret', email: 'joe@joe.com'},
                        {id : 2, name: "Gilbert", username: 'Gorday', email: 'joe@jane.com'},
                    ],
                    columns: ['id', 'name', 'username', 'email'],
                    searchSuccess: true,
                }
            }
        });

    }

    function stubFailedSearchRequest() {
        moxios.stubRequest('http://somelink.com', {
            status: 200,
            response: {
                success: 'true',
                reply: {
                    searchSuccess: false,
                    error: 'Search param \'joe\' Does not Match Any Name',
                }
            }
        });

    }

    function stubFailedRequest() {
        moxios.stubRequest('http://somelink.com', {
            status: 408,
            response: { message : 'Times Up Folk'},
        });
    }

    test('User\'s Table Display Correctly for Successful Request', (done) => {

        stubSuccessfulRequest();

        const wrapper = mount(UsersTable, {
            propsData: {
                nonce: '123456',
                ajaxUrl: 'http://somelink.com',
                search: {str: '', col: ''},
                hook: 'inpuserparser_hook',
            }
        });

        moxios.wait(() => {
            const tableWrapper = wrapper.get('#table-cover');
            let expectedStrings = ['John', 'joedoe', 'joe@joe.com', 'Jane', 'joejane', 'joe@jane.com'];
            expectedStrings.forEach((s) => expect(tableWrapper.text()).toContain(s));
            done();
        });

    })

    test('Errors Display Correctly for Failed Request', (done) => {

        stubFailedRequest();

        const wrapper = mount(UsersTable, {
            propsData: {
                nonce: '123456',
                ajaxUrl: 'http://somelink.com',
                search: {str: '', col: ''},
                hook: 'inpuserparser_hook',
            }
        });

        moxios.wait(() => {
            const tableWrapper = wrapper.get('#error');
            expect(tableWrapper.text()).toContain("408")
            done();
        });

    })

    test('UserTable Updates currentUserId and currentUserId Key when a user is clicked so to update user modal', (done) => {

        stubSuccessfulRequest();

        const wrapper = mount(UsersTable, {
            propsData: {
                nonce: '123456',
                ajaxUrl: 'http://somelink.com',
                search: {str: '', col: ''},
                hook: 'inpuserparser_hook',
            }
        });

        moxios.wait(() => {
            const tableWrapper = wrapper.get('#user-1').trigger('click');
            expect(wrapper.vm.currentUserId).toBe(1)
            expect(wrapper.vm.currentUserIdKey).toBe(1)
            done();
        });

    })

    test('UserTable Updates currentUserId and currentUserId Key when a user is clicked so to update user modal', (done) => {

        stubSuccessfulRequest();

        const wrapper = mount(UsersTable, {
            propsData: {
                nonce: '123456',
                ajaxUrl: 'http://somelink.com',
                search: {str: '', col: ''},
                hook: 'inpuserparser_hook',
            }
        });

        moxios.wait(() => {
            const tableWrapper = wrapper.get('#user-1').trigger('click');
            expect(wrapper.vm.currentUserId).toBe(1)
            expect(wrapper.vm.currentUserIdKey).toBe(1)
            done();
        });

    })


    test('Table is Updated Appropriately when search string is updated', (done) => {

        stubSearchRequest();

        const wrapper = mount(UsersTable, {
            propsData: {
                nonce: '123456',
                ajaxUrl: 'http://somelink.com',
                search: {str: 'jane', col: 'name'},
                hook: 'inpuserparser_hook',
            }
        });

        moxios.wait(() => {
            const tableWrapper = wrapper.get('#table-cover');
            let expectedStrings = ['Leane', 'Bret', 'joe@joe.com', 'Gilbert', 'Gorday', 'joe@jane.com'];
            expectedStrings.forEach((s) => expect(tableWrapper.text()).toContain(s));
            done();
        });

    })

    test('Table Shows Appropriate Error when Search Str is not found', (done) => {

        stubFailedSearchRequest();

        const wrapper = mount(UsersTable, {
            propsData: {
                nonce: '123456',
                ajaxUrl: 'http://somelink.com',
                search: {str: 'joe', col: 'name'},
                hook: 'inpuserparser_hook',
            }
        });

        moxios.wait(() => {
            const tableWrapper = wrapper.get('#error');
            expect(tableWrapper.text()).toBe('Search param \'joe\' Does not Match Any Name');
            done();
        });

    })

});


