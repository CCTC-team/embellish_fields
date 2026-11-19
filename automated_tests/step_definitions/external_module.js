//Add any of your own step definitions here
const { Given } = require('@badeball/cypress-cucumber-preprocessor')


/**
 * @module EmbellishFields/MonitoringQR
 * @author Mintoo Xavier <min2xavier@gmail.com>
 * @example I should see {string} within the data entry field labeled {string}
 * @param {string} fieldOptions - field options visible
 * @param {string} label - Field Label
 * @description verifies data entry field contains text
 */
Given("I should see {string} within the data entry field labeled {string}", (fieldOptions, label) => {
    cy.get('#questiontable').find('td').contains(label).parents('tr').should('contain', fieldOptions)
})
