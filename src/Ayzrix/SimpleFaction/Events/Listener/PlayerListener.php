<?php

/***
 *       _____ _                 _      ______         _   _
 *      / ____(_)               | |    |  ____|       | | (_)
 *     | (___  _ _ __ ___  _ __ | | ___| |__ __ _  ___| |_ _  ___  _ __
 *      \___ \| | '_ ` _ \| '_ \| |/ _ \  __/ _` |/ __| __| |/ _ \| '_ \
 *      ____) | | | | | | | |_) | |  __/ | | (_| | (__| |_| | (_) | | | |
 *     |_____/|_|_| |_| |_| .__/|_|\___|_|  \__,_|\___|\__|_|\___/|_| |_|
 *                        | |
 *                        |_|
 */

namespace Ayzrix\SimpleFaction\Events\Listener;

use Ayzrix\SimpleFaction\API\FactionsAPI;
use Ayzrix\SimpleFaction\Utils\Utils;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use pocketmine\world\format\Chunk;

class PlayerListener implements Listener {

    public function PlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        if (!FactionsAPI::hasLanguages($player)) {
            FactionsAPI::setLanguages($player, Utils::getIntoLang("default-language"));
        }
    }

    public function PlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        if ($player instanceof Player) {
            $cause = $player->getLastDamageCause();
            if ($cause instanceof EntityDamageByEntityEvent) {
                $damager = $cause->getDamager();
                if ($damager instanceof Player) {
                    if (FactionsAPI::isInFaction($damager->getName())) {
                        $dFaction = FactionsAPI::getFaction($damager->getName());
                        FactionsAPI::addPower($dFaction, (int)Utils::getIntoConfig("power_gain_per_kill"));
                    }
                }
            }

            if (FactionsAPI::isInFaction($player->getName())) {
                $pFaction = FactionsAPI::getFaction($player->getName());
                FactionsAPI::removePower($pFaction, (int)Utils::getIntoConfig("power_lost_per_death"));
            }
        }
    }

    public function PlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $item = $event->getItem();
        if (in_array($player->getWorld()->getFolderName(), Utils::getIntoConfig("faction_worlds"))) {
            $pos = $event->getBlock()->getPosition()->asVector3();
            $chunkX = $pos->getFloorX() >> Chunk::COORD_BIT_SIZE;
            $chunkZ = $pos->getFloorZ() >> Chunk::COORD_BIT_SIZE;
            if (FactionsAPI::isInClaim($player->getWorld(), $chunkX, $chunkZ)) {
                switch ($block->getTypeId()) {
                    case VanillaBlocks::ACACIA_FENCE_GATE():
                    case VanillaBlocks::BIRCH_FENCE_GATE():
                    case VanillaBlocks::DARK_OAK_FENCE_GATE():
                    case VanillaBlocks::CRIMSON_FENCE_GATE():
                    case VanillaBlocks::JUNGLE_FENCE_GATE():
                    case VanillaBlocks::MANGROVE_FENCE_GATE():
                    case VanillaBlocks::OAK_FENCE_GATE():
                    case VanillaBlocks::SPRUCE_FENCE_GATE():
                    case VanillaBlocks::WARPED_FENCE_GATE():
                    case VanillaBlocks::IRON_TRAPDOOR():
                    case VanillaBlocks::ACACIA_TRAPDOOR():
                    case VanillaBlocks::BIRCH_TRAPDOOR():
                    case VanillaBlocks::CRIMSON_TRAPDOOR():
                    case VanillaBlocks::DARK_OAK_TRAPDOOR():
                    case VanillaBlocks::JUNGLE_TRAPDOOR():
                    case VanillaBlocks::MANGROVE_TRAPDOOR():
                    case VanillaBlocks::OAK_TRAPDOOR():
                    case VanillaBlocks::SPRUCE_TRAPDOOR():
                    case VanillaBlocks::WARPED_TRAPDOOR():
                    case VanillaBlocks::CHEST():
                    case VanillaBlocks::TRAPPED_CHEST():
                    case VanillaBlocks::FURNACE():
                    case VanillaBlocks::IRON_DOOR():
                    case VanillaBlocks::ACACIA_DOOR():
                    case VanillaBlocks::BIRCH_DOOR():
                    case VanillaBlocks::DARK_OAK_DOOR():
                    case VanillaBlocks::JUNGLE_DOOR():
                    case VanillaBlocks::OAK_DOOR():
                    case VanillaBlocks::SPRUCE_DOOR():
                    case VanillaBlocks::CRIMSON_DOOR():
                    case VanillaBlocks::WARPED_DOOR():
                    case VanillaBlocks::MANGROVE_DOOR():
                    case VanillaBlocks::ENDER_CHEST():
                        if (FactionsAPI::isInFaction($player->getName())) {
                            $claimer = FactionsAPI::getFactionClaim($player->getWorld(), $chunkX, $chunkZ);
                            $faction = FactionsAPI::getFaction($player->getName());
                            if ($faction !== $claimer) $event->cancel();
                        } else $event->cancel();
                        break;
                }

                    switch ($item->getTypeId()) {
                        case VanillaItems::BUCKET():
                        case VanillaItems::DIAMOND_HOE():
                        case VanillaItems::GOLDEN_HOE():
                        case VanillaItems::IRON_HOE():
                        case VanillaItems::STONE_HOE():
                        case VanillaItems::WOODEN_HOE():
                        case VanillaItems::DIAMOND_SHOVEL():
                        case VanillaItems::GOLDEN_SHOVEL():
                        case VanillaItems::IRON_SHOVEL():
                        case VanillaItems::STONE_SHOVEL():
                        case VanillaItems::WOODEN_SHOVEL():
                            if (FactionsAPI::isInFaction($player->getName())) {
                                $claimer = FactionsAPI::getFactionClaim($player->getWorld(), $chunkX, $chunkZ);
                                $faction = FactionsAPI::getFaction($player->getName());
                                if ($faction !== $claimer) $event->cancel();
                            } else $event->cancel();
                            break;
                    }
            }
        }
    }

    public function PlayerChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        if (isset(FactionsAPI::$chat[$player->getName()])) {
            $chat = FactionsAPI::$chat[$player->getName()];
            switch ($chat) {
                case "FACTION":
                    $event->cancel();
                    FactionsAPI::factionMessage($player, $message);
                    break;
                case "ALLIANCE":
                    $event->cancel();
                    FactionsAPI::allyMessage($player, $message);
                    break;
            }
        }
    }
}