<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Controller;

use EffectConnect\Marketplaces\Core\Connection\ConnectionEntity;
use EffectConnect\Marketplaces\Service\ConnectionService;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingMigrationService;
use EffectConnect\Marketplaces\Setting\SettingStruct;
use Shopware\Core\Framework\Context;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\Acl;

/**
 * @RouteScope(scopes={"api"})
 */
class ConnectionController extends AbstractController
{
    protected ConnectionService $connectionService;
    protected SettingMigrationService $settingMigrationService;
    protected SalesChannelService $salesChannelService;

    public function __construct(ConnectionService $connectionService, SettingMigrationService $settingMigrationService, SalesChannelService $salesChannelService)
    {
        $this->connectionService = $connectionService;
        $this->settingMigrationService = $settingMigrationService;
        $this->salesChannelService = $salesChannelService;
    }

    /**
     * @Route(
     *     "api/effectconnect/action/connection/getAll",
     *     name="api.effectconnect.action.connection.getAll",
     *     methods={"GET"}
     * )
     * @Acl({"sales_channel.viewer"})
     */
    public function getAll(Request $request, Context $context): JsonResponse
    {
        if (!$this->settingMigrationService->isMigrated()) {
            $this->settingMigrationService->migrate();
        }

        $salesChannels = $this->salesChannelService->getSalesChannels();

        $connections = [];
        foreach($this->connectionService->getAll(['id','name','salesChannelId']) as $rawConnection) {

            $connection = new StdClass();
            $connection->id = $rawConnection->getId();
            $connection->name = $rawConnection->getName() ?: '-';
            $salesChannel = $salesChannels[$rawConnection->getSalesChannelId()];
            $connection->salesChannelReference = ($salesChannel !== null ? $salesChannel->getName() : '');
            $connections[] = $connection;
        }

        return new JsonResponse(['connections' => $connections]);
    }

    /**
     * @Route(
     *     "api/effectconnect/action/connection/get/{id}",
     *     name="api.effectconnect.action.connection.get",
     *     methods={"GET"}
     * )
     * @Acl({"sales_channel.viewer"})
     */
    public function getFromId(Request $request, Context $context): JsonResponse
    {
        $connection = $this->connectionService->getFromId($request->get('id'));
        $data = $connection->jsonSerialize();
        $toRemove = ['_uniqueIdentifier', 'versionId', 'translated', 'createdAt', 'updatedAt', 'extensions', 'customFields'];
        foreach($toRemove as $field) {
            unset($data[$field]);
        }
        return new JsonResponse(['connection' => $data]);
    }

    /**
     * @Route(
     *     "api/effectconnect/action/connection/delete/{id}",
     *     name="api.effectconnect.action.connection.delete",
     *     methods={"POST"}
     * )
     * @Acl({"sales_channel.viewer"})
     */
    public function delete(Request $request, Context $context): JsonResponse
    {
        $id = $request->get('id');
        $this->connectionService->delete($id);
        return new JsonResponse(['status' => 'OK']);
    }

    /**
     * @Route(
     *     "api/effectconnect/action/connection/getSalesChannelData",
     *     name="api.effectconnect.action.connection.getSalesChannelData",
     *     methods={"GET"}
     * )
     * @Acl({"sales_channel.viewer"})
     */
    public function getSalesChannels(Request $request, Context $context): JsonResponse
    {
        $salesChannelsInUse = array_map(function($c) {return $c->getSalesChannelId();},$this->connectionService->getAll(['salesChannelId']));
        $allSalesChannels = $this->salesChannelService->getSalesChannels();

        $toData = function($s) {return ['id' => $s->getId(), 'name' => $s->getName()];};

        $availableSalesChannels = [];
        foreach($allSalesChannels as $salesChannel) {
            if (!in_array($salesChannel->getId(), $salesChannelsInUse)) {
                $availableSalesChannels[] = $toData($salesChannel);
            }
        }

        return new JsonResponse([
            'available' => $availableSalesChannels,
            'all' => array_values(array_map($toData, $allSalesChannels))
        ]);
    }

    /**
     * @Route(
     *     "api/effectconnect/action/connection/getDefaultSettings",
     *     name="api.effectconnect.action.connection.getDefaultSettings",
     *     methods={"GET"}
     * )
     * @Acl({"sales_channel.viewer"})
     */
    public function getDefaultSettings(Request $request, Context $context): JsonResponse
    {
        $settings = (new SettingStruct());
        $data = [
            'catalogExportSchedule' => $settings->getCatalogExportSchedule(),
            'addLeadingZeroToEan' => $settings->isAddLeadingZeroToEan(),
            'useSpecialPrice' => $settings->isUseSpecialPrice(),
            'useFallbackTranslations' => $settings->isUseFallbackTranslations(),
            'useSalesChannelDefaultLanguageAsFirstFallbackLanguage' => $settings->isUseSalesChannelDefaultLanguageAsFirstFallbackLanguage(),
            'useSystemLanguages' => $settings->isUseSystemLanguages(),
            'offerExportSchedule' => $settings->getOfferExportSchedule(),
            'stockType' => $settings->getStockType(),
            'orderImportSchedule' => $settings->getOrderImportSchedule(),
            'paymentStatus' => $settings->getPaymentStatus(),
            'orderStatus' => $settings->getOrderStatus(),
        ];

        return new JsonResponse(['data' => $data]);
    }

    /**
     * @Route(
     *     "api/effectconnect/action/connection/save",
     *     name="api.effectconnect.action.connection.save",
     *     methods={"POST"}
     * )
     * @Acl({"sales_channel.viewer"})
     */
    public function save(Request $request, Context $context): JsonResponse
    {
        $rawConnection = $request->get('connection');
        $new = !isset($rawConnection['id']);
        $connection = (new ConnectionEntity())->assign($rawConnection);
        if ($new) {
            $this->connectionService->create($connection);
        } else {
            $this->connectionService->update($connection);
        }

        return new JsonResponse(['status' => 'OK', 'id' => $connection->getId()]);
    }

}