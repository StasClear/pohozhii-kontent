<?php

class modelRelevanter extends cmsModel {

    public function isFieldUnique($table_name, $field_name, $value, $val_row_name = 'id', $exclude_row_id = false) {

        $where = "({$field_name} = '{$value}')";

        if ($exclude_row_id) {
            $where .= " AND ({$val_row_name} = '{$exclude_row_id}')";
        }

        return !(bool) $this->db->getRowsCount($table_name, $where, 1);

    }

    public function addRelevant($relevant) {
        return $this->resetFilters()->insert('relevants', $relevant);
    }

    public function getRelevants() {
        return $this->get('relevants');
    }

    public function getRelevantByField($value, $field = 'id') {

        return $this->getItemByField('relevants', $field, $value, function($item, $model) {

            $item['content'] = cmsModel::yamlToArray($item['content']);
            $item['template'] = cmsModel::yamlToArray($item['template']);
            $item['fulltext'] = cmsModel::yamlToArray($item['fulltext']);
            $item['filters'] = $item['filters'] ? cmsModel::yamlToArray($item['filters']) : array();
            $item['sorting'] = $item['sorting'] ? cmsModel::yamlToArray($item['sorting']) : array();

            return $item;

        });

    }

    public function getRelevantsCount() {
        return $this->getCount('relevants');
    }

    public function toggleRelevantsVisibility($id, $is_visible) {

        return $this->update('relevants', $id, array(
            'is_visible' => $is_visible
        ));

    }

    public function deleteRelevant($id) {

        $this->delete('relevants', $id);
        cmsCache::getInstance()->clean('relevants');
        return true;

    }

    public function updateRelevant($id, $relevant) {

        $id = $this->update('relevants', $id, $relevant);
        cmsCache::getInstance()->clean('relevants');
        return $id;

    }

    public function getTagsIds($tags) {

        $ids = array();

        if (strpos($tags, ',') === false) {
            $ids[] = $this->filterEqual('tag', $tags)->getFieldFiltered('tags', 'id');
            return $ids;
        }

        $tags = explode(',', $tags);

        $this->filterStart();

        foreach ($tags as $tag) {
            $tag = trim($tag);
            $this->filterOr()->filterEqual('tag', $tag);
        }

        $this->filterEnd();

        $ids = $this->get('tags', function($item, $model) {
            return $item['id'];
        });

        return array_values($ids);

    }

    /*
     * Удаление компонента
     * @param int $id - id компонента
     * @return
     */

    public function deleteController($id) {

        // удаляем таблицу компонента
        $this->db->dropTable('relevants');

        $content_model = cmsCore::getModel('content');

        $ctypes = $content_model->getContentTypes();

        // удаляем поля в типах контента
        foreach ($ctypes as $id => $ctype) {

            $fields = $content_model->
                    filterIn('type', array('relevants', 'relevantsacross'))->
                    getContentFields($ctype['name']);

            if (!$fields) {
                continue;
            }

            foreach ($fields as $key => $field) {
                $content_model->deleteContentField($ctype['name'], $field['name'], 'name');
            }

        }

        // удаляем виджеты
        $widget = $this->filterEqual('controller', 'relevanter')->getItem('widgets');

        if ($widget) {
            $this->filterEqual('widget_id', $widget['id'])->deleteFiltered('widgets_bind');
        }

        $this->filterEqual('controller', 'relevanter')->deleteFiltered('widgets');

        // удаляем запись из cms_controllers
        return parent::deleteController($id);

    }

}
