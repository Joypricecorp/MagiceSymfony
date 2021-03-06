<?php

namespace Magice\Symfony\Form {

    use \Symfony\Component\Form\Form;

    /**
     * This software is provided as-is on the terms of MIT License.
     * Feel free to use, modify and share in ground of both non-commercial and commercial projects.
     *
     * What it does?
     * This service travels through all levels of form and gathers errors from every element.
     * Then it returns errors for you as array of errors - form errors, subforms errors and fields errors.
     * Notice that you need this approach to get ALL errors from form - also form (not fields) errors itself,
     * as well as errors from custom validators you have created.
     *
     *
     * Returned data is array of Symfony\Component\Form\FormError objects (default) or localized messages (use setReturnAsString() method)
     * It can be flat (one level) array (easier to iterate) as well as multidimensional array
     * that represents the structure of your form (easier to join form elements with errors).
     *
     * Why do I need this?
     * I wrote this service to be able to quickly get errors from any form and display them in one place.
     * Now it is my pleasure to share my code with you.
     *
     * @author Maciej Szkamruk <ex3v@ex3v.com>
     */
    class Errors
    {
        /**
         * This is the main method of service. Pass form object and call it to get resulting array.
         *
         * @param \Symfony\Component\Form\Form $form
         * @return array
         */
        public function parseErrors(Form $form)
        {
            $results = array();
            return $this->realParseErrors($form, $results);
        }

        /**
         * This does the actual job. Method travels through all levels of form recursively and gathers errors.
         * @param \Symfony\Component\Form\Form $form
         * @param array &$results
         */
        private function realParseErrors(Form $form, array &$results)
        {
            /*
             * first check if there are any errors bound for this element
             */
            $errors = $form->getErrors();

            if(count($errors)){
                $config = $form->getConfig();
                $name = $form->getName();
                $label = $config->getOption('label');
                $results[] = array('name' => $name, 'label' => $label, 'errors' => $errors);
            }

            /*
             * Then, check if there are any children. If yes, then parse them
             */

            $children = $form->all();

            if(count($children)){
                foreach($children as $child){
                    if($child instanceof Form){
                        $this->realParseErrors($child, $results);
                    }
                }
            }

            return $results;
        }
    }
}