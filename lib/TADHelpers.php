<?php
namespace TADPHP;

/**
 * TADHelpers: Abstract class that provides with several useful helpers. * 
 */
abstract class TADHelpers
{
  const XML_NO_DATA_FOUND = '<Row><Result>1</Result><Information>No data!</Information></Row>';
  
  /**
   * Parses an XML string searchig for datetime data to filter it based on date criteria supplied.
   * 
   * @param string $xml input XML string.
   * @param array $range date searching criteria.
   * @param string $xml_init_row_tag XML root tag.
   * @return string XML string filtered.
   */
  public static function filter_xml_by_date($xml, array $range=[], $xml_init_row_tag='<Row>')
  {
    $matches = [];
    $filtered_xml = self::XML_NO_DATA_FOUND;
    
    $rows = explode($xml_init_row_tag, $xml);
    $main_xml_init_tag = trim(array_shift($rows));
    $main_xml_end_tag = '' !== $main_xml_init_tag  ? '</' . str_replace('<', '', $main_xml_init_tag) : '';
    
    if('' !== $main_xml_end_tag){
      $rows[] = str_replace($main_xml_end_tag, '', array_pop($rows));
    }
    
    if( isset($range['start_date']) &&
        isset($range['end_date']) &&
        preg_match_all('/<DateTime>([0-9]{4}-[0-9]{2}-[0-9]{2}).+<\/DateTime>/', $xml, $matches) ){
      $indexes = array_keys( array_filter( $matches[1], function($date) use($range){ return !(strcmp($date, $range['start_date']) < 0 || strcmp($date, $range['end_date']) > 0);} ) );
      $filtered_xml =               
              (0 === count($indexes) ?
                      self::XML_NO_DATA_FOUND :
                      join( '', array_map( function($index) use($rows, $xml_init_row_tag){ return $xml_init_row_tag . $rows[$index]; }, $indexes ) )
              );
    }
    
    return $main_xml_init_tag . $filtered_xml . $main_xml_end_tag;
  }
  
  /**
   * Transforms an XML string into a JSON format.
   * 
   * @param string $xml_string input XML string.
   * @return string JSON string generated.
   */
  static public function xml_to_json($xml_string)
  {
    return !isset($xml_string) || '' === trim($xml_string) ? '{}' : json_encode(simplexml_load_string($xml_string));
  }
  
  /**
   * Transforms an XML string into an array.
   * 
   * @param string $xml_string input XML string.
   * @return array array generated.
   */
  static public function xml_to_array($xml_string)
  {
    return !isset($xml_string) || '' === trim($xml_string) ? [] : json_decode(self::xml_to_json($xml_string), true);
  }  
  
  /**
   * Transforms an array into an XML string.
   * 
   * The XML generated does not include <code><?xml version="1.0"?></code> tag.
   * 
   * @param \SimpleXMLElement $object <code>SimpleXMLElement</code> instance.
   * @param array $data input array to be transformed.
   * @return string XML string generated.
   */
  public static function array_to_xml(\SimpleXMLElement $object, array $data)
  {
    foreach ($data as $key => $value)
    {   
      if (is_array($value))
      {   
        $new_object = $object->addChild($key);
        self::array_to_xml($new_object, $value);
      }   
      else
      {   
        $object->addChild($key, $value);
      }   
    }
    
    $xml = trim( str_replace( [ "\n", "\r", "<?xml version=\"1.0\"?>" ], ' ', $object->asXML() ) );
    
    return $xml;
  }
  
  /**
   * Returns an array with all its items with values bases on wich ones are present in another array
   * supplied as input.
   * 
   * @param array $base base array with searching criterias.
   * @param array $values array values to be analized.
   * @param array $options tells if generated array should be an associative one.
   * @return array array generated.
   */
  public static function config_array_items(array $base, array $values, array $options=[])
  {
    $normalized_args = [];    
    $include_keys = isset($options['include_keys']) && $options['include_keys'];
    
    foreach($base as $parseable_arg_key){
      $normalized_args[$parseable_arg_key] = isset($values[$parseable_arg_key]) ? $values[$parseable_arg_key] : null;
    }
    
    return $include_keys ? $normalized_args : array_values($normalized_args);    
  }
}