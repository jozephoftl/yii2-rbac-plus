<?php

namespace johnitvn\rbacplus\models;

use Yii;
use yii\rbac\Item;
use johnitvn\rbacplus\AuthItemManager;

/**
 * Description of Permistion
 *
 * @author John Martin <john.itvn@gmail.com>
 * @since 1.0.0
 */
class Role extends AuthItem {

    public $permissions = [];

    public function init() {
        parent::init();
        if (!$this->isNewRecord) {
            $permissions = [];
            foreach (static::getPermistions($this->item->name) as $permission) {
                $permissions[] = $permission->name;
            }
            $this->permissions = $permissions;
        }
    }

    public function scenarios() {
        $scenarios = parent::scenarios();
        $scenarios['default'][] = 'permissions';
        return $scenarios;
    }

    protected function getType() {
        return Item::TYPE_ROLE;
    }

    public function afterSave($insert) {
        $authManager = Yii::$app->authManager;
        $role = $authManager->getRole($this->item->name);
        if (!$insert) {
            $authManager->removeChildren($role);
        }
        foreach ($this->permissions as $permissionName) {
            $permistion = $authManager->getPermission($permissionName);
            $authManager->addChild($role, $permistion);
        }
    }

    public function attributeLabels() {
        $labels = parent::attributeLabels();
        $labels['name'] = Yii::t('rbac', 'Role name');
        $labels['permissions'] = Yii::t('rbac', 'Permissions');
        return $labels;
    }

    public static function find($name) {
        return new self(AuthItemManager::getItem($name, Item::TYPE_ROLE));
    }

    public function getPermistions($name) {
        $authManager = Yii::$app->authManager;
        return $authManager->getPermissionsByRole($name);
    }

}
