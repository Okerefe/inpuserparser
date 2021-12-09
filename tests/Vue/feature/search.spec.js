/**
 * @jest-environment jsdom
 */
import { mount } from '@vue/test-utils'
import Search from '../../../public/js/src/Components/Search.vue'
import {beforeEach} from "@jest/globals";

describe('Search Component:', () => {

    let wrapper;
    beforeEach(function () {
        wrapper = mount(Search, {
            propsData: {
                searchByText: "Search By",
                fields: ["id","name","username"],
            }
        })

    })

    test('Displays "Search By" Text Correctly', () => {
        const searchInputWrapper = wrapper.get('.searchByText');
        expect(searchInputWrapper.element.value).toBe("Search By");
    })

    test('Displays All Search Fields in Select Tag', () => {
        expect(wrapper.find('option:nth-of-type(1)').element.value).toBe('id');
        expect(wrapper.find('option:nth-of-type(2)').element.value).toBe('name');
        expect(wrapper.find('option:nth-of-type(3)').element.value).toBe('username');
    })

    test('Emits Event with the right Data when search is Inputed', () => {
        const textInput = wrapper.find('input[aria-label="search"]')
        textInput.setValue('Joe')
        const options = wrapper.find('select').findAll('option')
        options.at(1).setSelected()


        expect(wrapper.find('option:checked').element.value).toBe('name')
        expect(wrapper.find('input[aria-label="search"]').element.value).toBe('Joe')

        expect(wrapper.emitted().searchstr).toBeTruthy();
        expect(wrapper.emitted().searchstr[0][0].str).toBe('Joe')
        expect(wrapper.emitted().searchstr[0][0].col).toBe('name')
    })

    test('Does not Emit Event When Input string < 3', () => {
        const textInput = wrapper.find('input[aria-label="search"]')
        textInput.setValue('Jo')
        const options = wrapper.find('select').findAll('option')
        options.at(1).setSelected()


        expect(wrapper.find('option:checked').element.value).toBe('name')
        expect(wrapper.find('input[aria-label="search"]').element.value).toBe('Jo')

        expect(wrapper.emitted().searchstr).toBeFalsy();
    })

    test('Emits Event When Input string changes to "" (empty string) so to reload table', () => {
        const textInput = wrapper.find('input[aria-label="search"]')
        textInput.setValue('')
        const options = wrapper.find('select').findAll('option')
        options.at(1).setSelected()


        expect(wrapper.find('option:checked').element.value).toBe('name')
        expect(wrapper.find('input[aria-label="search"]').element.value).toBe('')

        expect(wrapper.emitted().searchstr).toBeTruthy();
        expect(wrapper.emitted().searchstr[0][0].str).toBe('')
        expect(wrapper.emitted().searchstr[0][0].col).toBe('name')
    })

});