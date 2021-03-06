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
 *         'login' => [
 *             'class' => \umbalaconmeogia\ssosubdomain\actions\IdentityProviderLoginAction::class,
 *             'loginFormClass' => LoginForm::class,
 *         ],
 *     ];
 * }
 * </pre>
 * @author thanh
 *
 */
class IdentityProviderLoginAction extends Action
{
    /**
     * Session key to restore returnUrl.
     * @var string
     */
    public $sessionReturnUrlParam = 'loginReturnUrl';

    /**
     * Name of URL parameter to hold the returnUrl.
     * @var string
     */
    public $urlReturnUrlParam = 'returnUrl';

    /**
     * Class of login form model. 'app\models\LoginForm' for example.
     * @var string
     */
    public $loginFormClass;

    /**
     * View of login form.
     * @var string
     */
    public $view = 'login';

    /**
     * Initializes the application component.
     */
    public function init()
    {
        parent::init();

        if ($this->loginFormClass === null) {
            throw new InvalidConfigException('LoginAction::loginFormClass must be set.');
        }
    }

    /**
     * Return loginReturnUrl that is saved in session, also delete it from session.
     * @return string
     */
    private function popLoginReturnUrl()
    {
        $loginUrl = Yii::$app->session[$this->sessionReturnUrlParam];
        Yii::$app->session->remove($this->sessionReturnUrlParam);
        return $loginUrl;
    }

    public function run()
    {
        // Process URL parameter "returnUrl"
        $returnUrl = isset($_REQUEST[$this->urlReturnUrlParam]) ?
            $_REQUEST[$this->urlReturnUrlParam] : null;
        if ($returnUrl) { // If login is requested from sub-system.
            // Remember returnUrl into session.
            Yii::$app->session[$this->sessionReturnUrlParam] = $returnUrl;
        }

        // If user accesses to site/login directly from browser.
        if (!Yii::$app->user->isGuest) {
            // Redirect to returnUrl if is requested login from other sub-system.
            $returnUrl = $this->popLoginReturnUrl();
            if ($returnUrl) {
                return $this->controller->redirect($returnUrl);
            } else {
                return $this->controller->goHome();
            }
        }

        $model = new $this->loginFormClass;
        if ($model->load(Yii::$app->request->post()) && $model->login()) { // Login successfully
            // Redirect to returnUrl if is requested login from other sub-system.
            $returnUrl = $this->popLoginReturnUrl();
            if ($returnUrl) {
                return $this->controller->redirect($returnUrl);
            } else {
                return $this->controller->goBack();
            }
        }

        return $this->controller->render($this->view, [
            'model' => $model,
        ]);
    }
}