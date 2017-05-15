<?

namespace Oaki\Cart;

use Nette\Object;

;

class ErrorMsg extends Object
{

    private $minError = '';

    private $maxError = '';

    /**
     * ErrorMsg constructor.
     *
     */
    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function getMinError()
    {
        return $this->minError;
    }

    /**
     * @param string $minError
     */
    public function setMinError($minError)
    {
        $this->minError = $minError;
    }

    /**
     * @return string
     */
    public function getMaxError()
    {
        return $this->maxError;
    }

    /**
     * @param string $maxError
     */
    public function setMaxError($maxError)
    {
        $this->maxError = $maxError;
    }

}