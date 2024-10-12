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

    protected ?CurlyTemplate     $template = null;
    protected CallbackCollection $onParse;

    public function __construct(string $body = '{content}', string $subject = '', string $to = '', string $from = '')
    {
        parent::__construct($body, $subject, $to, $from);
        $this->onParse = new CallbackCollection();
        $this->set('content', '');
    }

    public static function create(string $body = '', string $subject = '', string $to = '', string $from = ''): self
    {
        return new self($body, $subject, $to, $from);
    }

    /**
     * Set the content. this should be the contents of the email
     * not to be confused with the message template.
     * It can contain curly template vars also.
     */
    public function setContent(string $tpl): static
    {
        $this->set('content', $tpl);
        return $this;
    }

    /**
     * The message text body
     */
    public function setBody(string $body): static
    {
        $this->body = $body;
        $this->template = null;
        if ($body)
            $this->template = CurlyTemplate::create($body);
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
     * Gets the tCurlyTemplate object
     * This will return null until the setBody($body) function is called with data
     */
    public function getTemplate(): ?CurlyTemplate
    {
        return $this->template;
    }

    /**
     * Returns the parsed message body ready for sending.
     */
    public function getParsed(): string
    {
        if (!$this->template) return '';

        $this->set('subject', $this->getSubject());
        $this->set('fromEmail', $this->getFrom());
        $this->set('toEmail', self::listToStr($this->getTo()));
        $this->set('toEmailList', self::listToStr($this->getTo()));
        $this->set('ccEmailList', self::listToStr($this->getCc()));
        $this->set('bccEmailList', self::listToStr($this->getBcc()));
        $this->set('date', Date::create()->format(Date::FORMAT_LONG_DATETIME));

        $this->getOnParse()->execute($this);

        return $this->template->parse($this->getCollection()->all());
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