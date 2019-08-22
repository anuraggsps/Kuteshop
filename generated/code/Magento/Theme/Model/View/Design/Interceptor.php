<?php
namespace Magento\Theme\Model\View\Design;

/**
 * Interceptor class for @see \Magento\Theme\Model\View\Design
 */
class Interceptor extends \Magento\Theme\Model\View\Design implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\View\Design\Theme\FlyweightFactory $flyweightFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Theme\Model\ThemeFactory $themeFactory, \Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Framework\App\State $appState, array $themes)
    {
        $this->___init();
        parent::__construct($storeManager, $flyweightFactory, $scopeConfig, $themeFactory, $objectManager, $appState, $themes);
    }

    /**
     * {@inheritdoc}
     */
    public function setArea($area)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setArea');
        if (!$pluginInfo) {
            return parent::setArea($area);
        } else {
            return $this->___callPlugins('setArea', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getArea()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getArea');
        if (!$pluginInfo) {
            return parent::getArea();
        } else {
            return $this->___callPlugins('getArea', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDesignTheme($theme, $area = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setDesignTheme');
        if (!$pluginInfo) {
            return parent::setDesignTheme($theme, $area);
        } else {
            return $this->___callPlugins('setDesignTheme', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationDesignTheme($area = null, array $params = [])
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getConfigurationDesignTheme');
        if (!$pluginInfo) {
            return parent::getConfigurationDesignTheme($area, $params);
        } else {
            return $this->___callPlugins('getConfigurationDesignTheme', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultDesignTheme()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setDefaultDesignTheme');
        if (!$pluginInfo) {
            return parent::setDefaultDesignTheme();
        } else {
            return $this->___callPlugins('setDefaultDesignTheme', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDesignTheme()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getDesignTheme');
        if (!$pluginInfo) {
            return parent::getDesignTheme();
        } else {
            return $this->___callPlugins('getDesignTheme', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getThemePath(\Magento\Framework\View\Design\ThemeInterface $theme)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getThemePath');
        if (!$pluginInfo) {
            return parent::getThemePath($theme);
        } else {
            return $this->___callPlugins('getThemePath', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getLocale');
        if (!$pluginInfo) {
            return parent::getLocale();
        } else {
            return $this->___callPlugins('getLocale', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(\Magento\Framework\Locale\ResolverInterface $locale)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setLocale');
        if (!$pluginInfo) {
            return parent::setLocale($locale);
        } else {
            return $this->___callPlugins('setLocale', func_get_args(), $pluginInfo);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDesignParams()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getDesignParams');
        if (!$pluginInfo) {
            return parent::getDesignParams();
        } else {
            return $this->___callPlugins('getDesignParams', func_get_args(), $pluginInfo);
        }
    }
}
