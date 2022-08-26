<?php declare(strict_types=1);

namespace EffectConnect\Marketplaces\Controller;

use EffectConnect\Marketplaces\Core\Connection\ConnectionEntity;
use EffectConnect\Marketplaces\Exception\InvalidApiCredentialsException;
use EffectConnect\Marketplaces\Service\Api\CredentialService;
use EffectConnect\Marketplaces\Service\ConnectionService;
use EffectConnect\Marketplaces\Service\SalesChannelService;
use EffectConnect\Marketplaces\Service\SettingMigrationService;
use EffectConnect\Marketplaces\Setting\SettingStruct;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Framework\Context;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractConnectionController extends AbstractController
{
    /** @var ConnectionService */
    protected $connectionService;
    /** @var SettingMigrationService */
    protected $settingMigrationService;
    /** @var SalesChannelService */
    protected $salesChannelService;
    /** @var CredentialService */
    protected $credentialService;

    public function __construct(ConnectionService $connectionService, SettingMigrationService $settingMigrationService, SalesChannelService $salesChannelService, CredentialService $credentialService)
    {
        $this->connectionService = $connectionService;
        $this->settingMigrationService = $settingMigrationService;
        $this->salesChannelService = $salesChannelService;
        $this->credentialService = $credentialService;
    }

    /**
     * @Route("/getAll", methods={"GET"})
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
     * @Route("/get/{id}", methods={"GET"})
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
     * @Route("/delete/{id}", methods={"POST"})
     */
    public function delete(Request $request, Context $context): JsonResponse
    {
        $id = $request->get('id');
        $this->connectionService->delete($id);
        return new JsonResponse(['status' => 'OK']);
    }

    /**
     * @Route("/getSalesChannelData", methods={"GET"})
     */
    public function getSalesChannels(Request $request, Context $context): JsonResponse
    {
        $allSalesChannels = $this->salesChannelService->getSalesChannels();
        $toData = function($s) {return ['value' => $s->getId(), 'label' => $s->getName()];};
        return new JsonResponse([
            'data' => array_values(array_map($toData, $allSalesChannels))
        ]);
    }

    /**
     * @Route("/getDefaultSettings", methods={"GET"})
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
     * @Route("/getOptions", methods={"GET"})
     */
    public function getOptions(Request $request, Context $context): JsonResponse
    {
        $data = [
            'order' => [
                OrderStates::STATE_OPEN,
                OrderStates::STATE_IN_PROGRESS,
            ],
            'payment' => [
                OrderTransactionStates::STATE_OPEN,
                OrderTransactionStates::STATE_PAID,
            ],
            'stockTypes' => [
                SettingStruct::STOCK_TYPE_PHYSICAL,
                SettingStruct::STOCK_TYPE_SALABLE,
            ],
            'schedules' => [
                86400, 64800, 43200, 21600, 3600, 1800, 900, 300, 0
            ],
        ];


        return new JsonResponse(['data' => $data]);
    }

    /**
     * @Route("/save", methods={"POST"})
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

    /**
     * @Route("/testApiCredentials", methods={"POST"})
     */
    public function testApiCredentials(Request $request, Context $context): JsonResponse
    {

        $publicKey  = $request->get('publicKey');
        $secretKey  = $request->get('secretKey');
        $valid      = $this->credentialService->checkApiCredentials($publicKey, $secretKey);
        return new JsonResponse(['valid' => $valid]);
    }

}