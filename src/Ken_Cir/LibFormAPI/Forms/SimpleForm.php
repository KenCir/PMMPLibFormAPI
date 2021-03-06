<?php

declare(strict_types=1);

namespace Ken_Cir\LibFormAPI\Forms;

use JetBrains\PhpStorm\ArrayShape;
use Ken_Cir\LibFormAPI\FormContents\SimpleForm\SimpleFormButton;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class SimpleForm extends BaseForm
{
    /**
     * Form Title
     *
     * @var string
     */
    private string $title;

    /**
     * Form Content
     * @var string
     */
    private string $content;

    /**
     * @var string[] Form Buttons
     */
    private array $buttons;

    /**
     * @param Player $player
     * @param string $title
     * @param string $content
     * @param SimpleFormButton[] $buttons
     * @param callable $responseHandle
     * @param callable|null $closeHandler
     */
    public function __construct(PluginBase $plugin, Player $player, string $title, string $content, array $buttons, callable $responseHandle, callable $closeHandler = null)
    {
        parent::__construct($plugin, $player, $responseHandle, $closeHandler);

        $this->title = $title;
        $this->content = $content;
        $this->buttons = $buttons;

        $player->sendForm($this);
    }

    public function handleResponse(Player $player, $data): void
    {
        if ($data === null) $this->close();
        // もしint以外の型が返された場合
        elseif (!is_int($data)) throw new FormValidationException("I expected a response of int but " . gettype($data) . " was returned");
        // 範囲外だ
        elseif ($data < 0 || count($this->buttons) <= $data) throw new FormValidationException("Out of range $data");
        else $this->response($data);
    }

    #[ArrayShape(["type" => "string", "title" => "string", "content" => "string", "buttons" => "false|string"])]
    public function jsonSerialize(): array
    {
        return array(
            "type" => "form",
            "title" => $this->title,
            "content" => $this->content,
            "buttons" => json_decode(json_encode($this->buttons), true)
        );
    }

    public function reSend(): void
    {
        $this->getPlayer()->sendForm($this);
    }
}