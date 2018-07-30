<?php

namespace ACFBCore{

    class ACF_buddy {
        
        private $clean_sections = [];
        private $clean_requested_sections=[]; 
        /**
         * @param Array containing the string name of the section you wish to request
         * if no param is passed it will return all fields
         */
        public function prepare_sections($requested_sections = [], $phpunit=false){


            if(!$phpunit){
                $field_groups = \acf_get_field_groups();
            }else{
                $layout = $this->create_dummy_layout();
                $layout_type = "mock_partial";
            }
            
            
            if(isset($field_groups)){

                foreach ( $field_groups as $group ) {
                    // DO NOT USE: $fields = acf_get_fields($group['key']);
                    // because it causes repeater field bugs and returns "trashed" fields
                    $sections = get_posts(array(
                        'posts_per_page'   => -1,
                        'post_type'        => 'acf-field',
                        'orderby'          => 'menu_order',
                        'order'            => 'ASC',
                        'suppress_filters' => true, // DO NOT allow WPML to modify the query
                        'post_parent'      => $group['ID'],
                        'post_status'      => 'any',
                        'update_post_meta_cache' => false
                    ));
                    
                    $this->clean_sections = [];
                    foreach ( $sections as $section ) {
                        
                 
                        $layout = get_field($section->post_name);
                        $layout_type = $section->post_excerpt;
                      
    
                        $this->process_layout($layout, $layout_type, $requested_sections);
                        
                        
                    }
                }
                
   
                
                return $this->clean_sections;
            }

            if($phpunit){
                $this->process_layout($layout, $layout_type);
            }

            return $this->clean_sections;  
        }
    
    
        /**
         * @param Array of sections mapped => to their layout => values
         * @param Array string names of sections to exclude
         */
        public function render_fields($partials, $exclude = []){

            foreach($partials as $section ){
                $section_type = $section['acf_fc_layout'];
         
                if($section_type !== 'page_components' && !in_array($section_type, $exclude)){
                    $folder = '/partials/sections/' . str_replace('_', '-', $section['acf_fc_layout'] ) . "/";
                    
                    foreach($section[$section_type] as $contents){
                        $path = $folder . str_replace('_', '-', $contents['acf_fc_layout'] ) . '.php';
                        include(locate_template( $path ));	                   
                    }
                }
    
            }

        }

        public function process_layout($layout,$layout_type, $requested_sections=[]){
        

            if($layout_type !== 'page_components'){
                
                foreach($layout as $index => $partial){
          
                    foreach($partial["section_layout"] as $field_values){
                        
                        if(is_null($field_values)){
                            $field_values = "";
                        }

                        

                        // TODO: further abstract
                        $section = $partial['acf_fc_layout'];
                        if( count($requested_sections) == 0){
                            
                            if( $index <= ( count($layout) - 1 )){
                                array_push( $this->clean_sections, array());
                            }
                            
                            $this->add_to_clean_sections($index, $section, $field_values);

                        }else{
                          
                            if( in_array( $section, $requested_sections ) ){
                                

                                if($index <= ( count($layout) - 1 )){
                                    array_push( $this->clean_sections, array());
                                }

                                $this->add_to_clean_sections($index, $section, $field_values);
                            
                            }
                        }
                   
                    }
                }

            }

            return $this->clean_sections;
                
             
        }


        public function add_to_clean_sections($index, $section, $field_values ){
            $this->clean_sections[$index]["acf_fc_layout"] = $section;
            $this->clean_sections[$index][$section][] = $field_values;
        }

        public function create_dummy_layout(){
            return array(
                    
                array(
                    "acf_fc_layout" => "mock_partial",
                    "section_layout" => array(
                        array(
                            "acf_fc_layout" => "mock_section",
                            "mock_section" => array(
                                array(
                                    "acf_fc_layout" => "section_contents",
                                    "section_contents" => array(
                                        "data" => "data"
                                    ),
                                )
                            ),
                        ),
                    ),
                ),
                array(
                    "acf_fc_layout" => "mock_partial",
                    "section_layout" => array(
                        array(
                            "acf_fc_layout" => "mock_section",
                            "mock_section" => array(
                                array(
                                    "acf_fc_layout" => "section_contents",
                                    "section_contents" => array(
                                        "data" => "data"
                                    ),
                                )
                            ),
                        ),
                    ),
                ),
                array(
                    "acf_fc_layout" => "mock_partial",
                    "section_layout" => array(
                        array(
                            "acf_fc_layout" => "mock_section",
                            "mock_section" => array(
                                array(
                                    "acf_fc_layout" => "section_contents",
                                    "section_contents" => array(
                                        "data" => "data"
                                    ),
                                )
                            ),
                        ),
                    ),
                ),
            );
        }

    } // end class
} // end namespace
