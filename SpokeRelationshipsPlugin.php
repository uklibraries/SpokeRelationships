<?php
/**
 * Spoke Relationships
 * @copyright 2015 Michael Slone
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Spoke Relationships plugin.
 */
class SpokeRelationshipsPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Hooks for the plugin.
     */
    #protected $_hooks = array(
    #);

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array(
        'admin_items_form_tabs',
    );

    /**
     * Add the "Item Relations" tab to the admin items add/edit page.
     *
     * @return array
     */
    public function filterAdminItemsFormTabs($tabs, $args)
    {
        $item = $args['item'];

        $formSelectProperties = get_table_options('ItemRelationsProperty');
        $subjectRelations = self::prepareSubjectRelations($item);
        $objectRelations = self::prepareObjectRelations($item);

        ob_start();
        include 'spoke_relationships_form.php';
        $content = ob_get_contents();
        ob_end_clean();

        $tabs['Item Relations'] = $content;
        return $tabs;
    }

    /**
     * Prepare subject item relations for display.
     *
     * @param Item $item
     * @return array
     */
    public static function prepareSubjectRelations(Item $item)
    {
        $subjects = get_db()->getTable('ItemRelationsRelation')->findBySubjectItemId($item->id);
        $subjectRelations = array();

        foreach ($subjects as $subject) {
            if (!($item = get_record_by_id('item', $subject->object_item_id))) {
                continue;
            }
            $subjectRelations[] = array(
                'item_relation_id' => $subject->id,
                'object_item_id' => $subject->object_item_id,
                'object_item_title' => self::getItemTitle($item),
                'relation_text' => $subject->getPropertyText(),
                'relation_description' => $subject->property_description
            );
        }
        return $subjectRelations;
    }

    /**
     * Prepare object item relations for display.
     *
     * @param Item $item
     * @return array
     */
    public static function prepareObjectRelations(Item $item)
    {
        $objects = get_db()->getTable('ItemRelationsRelation')->findByObjectItemId($item->id);
        $objectRelations = array();
        foreach ($objects as $object) {
            if (!($item = get_record_by_id('item', $object->subject_item_id))) {
                continue;
            }
            $objectRelations[] = array(
                'item_relation_id' => $object->id,
                'subject_item_id' => $object->subject_item_id,
                'subject_item_title' => self::getItemTitle($item),
                'relation_text' => $object->getPropertyText(),
                'relation_description' => $object->property_description
            );
        }
        return $objectRelations;
    }

    /**
     * Return a item's title.
     *
     * @param Item $item The item.
     * @return string
     */
    public static function getItemTitle($item)
    {
        $title = metadata($item, array('Dublin Core', 'Title'), array('no_filter' => true));
        if (!trim($title)) {
            $title = '#' . $item->id;
        }
        return $title;
    }
}
