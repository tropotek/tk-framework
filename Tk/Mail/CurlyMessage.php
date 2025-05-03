<?php
namespace Tk\Mail;

use Tk\CallbackCollection;
use Tk\Traits\CollectionTrait;
use Tk\CurlyTemplate;
use Tk\Date;

/**
 * This message accepts text templates and replaces {param} with the
 * corresponding value.
 */
class CurlyMessage extends Message
{
    use CollectionTrait;

    protected string             $content = '';
    protected ?CurlyTemplate     $template = null;
    protected CallbackCollection $onParse;

    public function __construct(string $body = '{content}', string $subject = '', string $to = '', string $from = '')
    {
        parent::__construct($body, $subject, $to, $from);
        $this->onParse = new CallbackCollection();
    }

    public static function create(string $body = '', string $subject = '', string $to = '', string $from = ''): self
    {
        return new self($body, $subject, $to, $from);
    }

    /**
     * Set the content. this should be the contents of the email
     * not to be confused with the message body template text.
     * It can contain curly template vars.
     */
    public function setContent(string $tpl): static
    {
        $this->content = $tpl;
        return $this;
    }

    public function getOnParse(): CallbackCollection
    {
        return $this->onParse;
    }

    /**
     * Set a callback function to fire when the getParsed() method is called
     * EG: function ($curlyMessage) { }
     */
    public function addOnParse(callable $callable, int $priority = CallbackCollection::DEFAULT_PRIORITY): static
    {
        $this->getOnParse()->append($callable, $priority);
        return $this;
    }

    /**
     * Returns the parsed message body ready for sending.
     */
    public function getParsed(): string
    {
        $this->set('subject', $this->getSubject());
        $this->set('fromEmail', $this->getFrom());
        $this->set('toEmail', self::listToStr($this->getTo()));
        $this->set('toEmailList', self::listToStr($this->getTo()));
        $this->set('ccEmailList', self::listToStr($this->getCc()));
        $this->set('bccEmailList', self::listToStr($this->getBcc()));
        $this->set('date', Date::create()->format(Date::FORMAT_LONG_DATETIME));

        // TODO: remove this once all code uses $template->setContent('')
        if ($this->has('content')) {
            $this->setContent($this->get('content'));
            $this->remove('content');
        }

        $tpl = $this->getBody();
        $tpl = str_replace('{content}', $this->content, $tpl);
        $template = CurlyTemplate::create($tpl);

        $this->getOnParse()->execute($this, $template);

        return $template->parse($this->getCollection()->all());
    }

    /**
     * Return an array of curly template params and descriptions
     */
    public static function getParamList(): array
    {
        return [
            'subject' => '{string}',
            'fromEmail' => 'from@example.com',
            'toEmail' => 'email1@example.com, email2@example.com, ..',
            'toEmailList' => 'email1@example.com, email2@example.com, ..',
            'ccEmailList' => 'email1@example.com, email2@example.com, ..',
            'bccEmailList' => 'email1@example.com, email2@example.com, ..',
            'date' => 'Tuesday, 01 Jan 2009 12:59 PM',
        ];
    }

}