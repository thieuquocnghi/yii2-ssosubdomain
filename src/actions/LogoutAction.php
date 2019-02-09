<?php
namespace umbalaconmeogia\ssosubdomain\actions;

use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;

/**
 * Example of usage. In SiteController class, declare the action.
 * <pre>
 * public function actions()
 * {
 *     return [
 *         // Another definition.
 *         'logout' => [
 *             'class' => \umbalaconmeogia\ssosubdomain\actions\LogoutAction::class,
 *             'ssoCookieDomain' => Yii::$app->session->cookieParams['domain'],
 *         ],
 *     ];
 * }
 * </pre>
 * @author thanh
 *
 */
class LogoutAction extends Action
{
    /**
     * Name of URL parameter to hold the returnUrl.
     * @var string
     */
    public $urlReturnUrlParam = 'returnUrl';

    /**
     *
     * @var string
     */
    public $ssoSessionIdPrefix = 'sso-sessid-';

    /**
     * Root domain, in which cookie stores login identity.
     * @var string
     */
    public $ssoCookieDomain;

    /**
     * Initializes the application component.
     */
    public function init()
    {
        parent::init();

        if ($this->ssoCookieDomain === null) {
            throw new InvalidConfigException('LogoutAction::ssoCookieDomain must be set.');
        }
    }

    public function run()
    {
        // Process URL parameter "returnUrl"
        $returnUrl = isset($_REQUEST[$this->urlReturnUrlParam]) ?
            $_REQUEST[$this->urlReturnUrlParam] : null;

        Yii::$app->user->logout();

        // Clear session id key of all sub-domains in cookie.
        foreach ($_COOKIE as $key => $value) {
            if (strpos($key, $this->ssoSessionIdPrefix) !== FALSE) {
                setcookie($key, $value, time() - 3600, null, $this->ssoCookieDomain);
            }
        };

        // Redirect to returnUrl if is requested login from other sub-system.
        if ($returnUrl) {
            return $this->controller->redirect($returnUrl);
        } else {
            return $this->controller->goHome();
        }
    }
}