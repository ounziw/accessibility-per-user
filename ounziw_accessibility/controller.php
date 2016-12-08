<?php 
namespace Concrete\Package\OunziwAccessibility;

use View;
use User;
use UserInfo;
use UserAttributeKey;
use Events;
use \Concrete\Core\Attribute\Type as AttributeType;

defined('C5_EXECUTE') or die("Access Denied.");

class Controller extends \Concrete\Core\Package\Package {

    protected $pkgHandle = 'ounziw_accessibility';
    protected $appVersionRequired = '5.7.4';
    protected $pkgVersion = '1.0';

    public function getPackageDescription() {
        return t("Allows users to enable Toolbar Title / Large Font.");
    }

    public function getPackageName() {
        return t("Accessibility per User");
    }

    public function install()
    {
        $pkg = parent::install();

        // user-attribute
        $ounziw_toolbar_titles = UserAttributeKey::getByHandle('ounziw_toolbar_titles');
        if (is_object($ounziw_toolbar_titles)) {
            if (!$ounziw_toolbar_titles->isAttributeKeyActive()) {
                $ounziw_toolbar_titles->activate();
            }
        } else {
            $type = AttributeType::getByHandle('boolean');
            $args = array(
                'akHandle' => 'ounziw_toolbar_titles',
                'akName' => t('Enable Toolbar Titles'),
                'uakRegisterEdit' => 1,
                'uakProfileEdit' => 1,
            );
            UserAttributeKey::add($type,$args);
        }

        // user-attribute
        $ounziw_large_font = UserAttributeKey::getByHandle('ounziw_large_font');
        if (is_object($ounziw_large_font)) {
            if (!$ounziw_large_font->isAttributeKeyActive()) {
                $ounziw_large_font->activate();
            }
        } else {
            $type = AttributeType::getByHandle('boolean');
            $args = array(
                'akHandle' => 'ounziw_large_font',
                'akName' => t('Increase Toolbar Font Size'),
                'uakRegisterEdit' => 1,
                'uakProfileEdit' => 1,
            );
            UserAttributeKey::add($type,$args);
        }
    }

    public function uninstall()
    {

        $ounziw_toolbar_titles = UserAttributeKey::getByHandle('ounziw_toolbar_titles');
        if (is_object($ounziw_toolbar_titles)) {
            $ounziw_toolbar_titles->deactivate();
        }

        $ounziw_large_font = UserAttributeKey::getByHandle('ounziw_large_font');
        if (is_object($ounziw_large_font)) {
            $ounziw_large_font->deactivate();
        }


        parent::uninstall();
    }

    public function on_start() {
        Events::addListener('on_page_view', array($this,'on_page_view'));
    }

    public function on_page_view(){
        // register javascripts
        $al = \Concrete\Core\Asset\AssetList::getInstance();
        $al->register(
            'javascript', 'title_true_js', 'js/title_true.js', array(), $this->pkgHandle
        );
        $al->register(
            'javascript', 'title_false_js', 'js/title_false.js', array(), $this->pkgHandle
        );
        $al->register(
            'javascript', 'large_font_true_js', 'js/large_font_true.js', array(), $this->pkgHandle
        );
        $al->register(
            'javascript', 'large_font_false_js', 'js/large_font_false.js', array(), $this->pkgHandle
        );

        $u = new User();
        if (is_object($u) && $u->checkLogin()) {
            $uinfo = UserInfo::getByID($u->uID);
            
            $v = View::getInstance();
            // js adds/removes class="titles"
            if ($uinfo->getAttribute('ounziw_toolbar_titles')) {
                $v->requireAsset('javascript', 'title_true_js');
            } else {
                $v->requireAsset('javascript', 'title_false_js');
            }
            // js adds/removes class="large-font"
            if ($uinfo->getAttribute('ounziw_large_font')) {
                $v->requireAsset('javascript', 'large_font_true_js');
            } else {
                $v->requireAsset('javascript', 'large_font_false_js');
            }
        }
    }
}