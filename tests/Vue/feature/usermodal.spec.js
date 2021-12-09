/**
 * @jest-environment jsdom
 */
import { mount } from '@vue/test-utils'
import UserModal from '../../../public/js/src/Components/UserModal.vue'
import moxios from 'moxios'
import {afterEach, beforeEach} from "@jest/globals";

describe('UserModal Component:', () => {
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
                reply: { id: "1", name: "Leanne Graham", username: "Bret", email: "Sincere@april.biz", street: "Kulas Light", suite: "Apt. 556", city: "Gwenborough", zipcode: "92998-3874", lat: "-37.3159", lng: "81.1496", phone: "1-770-736-8031 x56442", website: "hildegard.org", companyName: "Romaguera-Crona", companyCatchPhrase: "Multi-layered client-server neural-net",companyBs: "harness real-time e-markets"},
            }
        });

    }


    test('User Modal Does not Display It\'s Content when props UserId is 0', (done) => {

        stubSuccessfulRequest();

        const wrapper = mount(UserModal, {
            propsData: {
                userId: 0,
                nonce: '123456',
                hook: 'inpuserparser_hook',
                ajaxUrl: 'http://somelink.com',
            }
        });

        moxios.wait(() => {
            expect(wrapper.html()).toBe('');
            done();
        });

    })

    test('User Modal Displays All Required Strings When Request is Made', (done) => {

        stubSuccessfulRequest();

        const wrapper = mount(UserModal, {
            propsData: {
                userId: 1,
                nonce: '123456',
                hook: 'inpuserparser_hook',
                ajaxUrl: 'http://somelink.com',
            }
        });

        moxios.wait(() => {
            const modal = wrapper.get('.user-modal-backdrop');

            let expectedStrings = ["Leanne Graham", "Bret", "Sincere@april.biz", "Kulas Light", "Apt. 556", "Gwenborough", "92998-3874", "-37.3159", "81.1496","1-770-736-8031 x56442", "hildegard.org", "Romaguera-Crona", "Multi-layered client-server neural-net", "harness real-time e-markets"];
            expectedStrings.forEach((s) => expect(modal.text()).toContain(s));
            done();
        });

    })
    test('User Modal Displays Website Link and \'find on map\' link correctly', (done) => {

        stubSuccessfulRequest();

        const wrapper = mount(UserModal, {
            propsData: {
                userId: 1,
                nonce: '123456',
                hook: 'inpuserparser_hook',
                ajaxUrl: 'http://somelink.com',
            }
        });

        moxios.wait(() => {
            const modal = wrapper.get('.user-modal-backdrop');

            let expectedStrings = ["https://maps.google.com/?q=-37.3159,81.1496", "http://hildegard.org",];
            expectedStrings.forEach((s) => expect(modal.html()).toContain(s));
            done();
        });

    })


});


