<?php

namespace App\Controllers\Api\V1;

use App\Models\UploadModel;
use App\Models\InsightModel;
use CodeIgniter\HTTP\ResponseInterface;

class AnalyticsController extends BaseApiController
{
    public function financialOverview(): ResponseInterface
    {
        $modUpload = new UploadModel();
        $dated = date('Y-m-d H:i:s');
        $token = (string) $this->request->getPost('varUser');
        $devId = (string) $this->request->getPost('varDev');

        try {
            $this->auditApiCall('get/my_financial_overview', $token, $devId);
            $overview = $modUpload->get_financial_overview($token, $devId);

            return $this->respond([
                'status' => self::STATUS_SUCCESS,
                'time' => $dated,
                'overview' => $overview
            ]);
        } catch (\Throwable $e) {
            return $this->respond([
                'status' => self::STATUS_ERROR,
                'time' => $dated,
                'message' => 'Failed to retrieve financial overview'
            ]);
        }
    }

    public function transactionsByCategory(): ResponseInterface
    {
        $modUpload = new UploadModel();
        $dated = date('Y-m-d H:i:s');
        $token = (string) $this->request->getPost('varUser');
        $devId = (string) $this->request->getPost('varDev');
        $category = (string) $this->request->getPost('varCategory');
        $page = (int) ($this->request->getPost('varPage') ?: 1);
        $perPage = (int) ($this->request->getPost('varPerPage') ?: 50);

        try {
            $this->auditApiCall('get/my_transactions_by_category', $token, $devId);
            $result = $modUpload->get_transactions_by_category($token, $devId, $category, $page, $perPage);

            return $this->respond([
                'status' => self::STATUS_SUCCESS,
                'time' => $dated,
                'transactions' => $result['transactions'],
                'total' => $result['total'],
                'page' => $result['page'],
                'per_page' => $result['per_page'],
            ]);
        } catch (\Throwable $e) {
            return $this->respond([
                'status' => self::STATUS_ERROR,
                'time' => $dated,
                'message' => 'Failed to retrieve transactions'
            ]);
        }
    }

    public function senderProfiles(): ResponseInterface
    {
        $modUpload = new UploadModel();
        $dated = date('Y-m-d H:i:s');
        $token = (string) $this->request->getPost('varUser');
        $devId = (string) $this->request->getPost('varDev');

        try {
            $this->auditApiCall('get/my_sender_profiles', $token, $devId);
            $profiles = $modUpload->get_sender_profiles($token, $devId);

            return $this->respond([
                'status' => self::STATUS_SUCCESS,
                'time' => $dated,
                'profiles' => $profiles
            ]);
        } catch (\Throwable $e) {
            return $this->respond([
                'status' => self::STATUS_ERROR,
                'time' => $dated,
                'message' => 'Failed to retrieve sender profiles'
            ]);
        }
    }

    public function financialHealth(): ResponseInterface
    {
        $token = (string) $this->request->getPost('varUser');
        if (empty($token)) return $this->failUnauthorized('User token required');

        $insights = new InsightModel();
        $scoreData = $insights->getFinancialHealthScore($token);

        return $this->respond([
            'status' => self::STATUS_SUCCESS,
            'score'  => (int)$scoreData['score'],
            'color'  => $scoreData['color'],
            'tips'   => $scoreData['tips']
        ]);
    }

    public function financialAlerts(): ResponseInterface
    {
        $token = (string) $this->request->getPost('varUser');
        if (empty($token)) return $this->failUnauthorized('User token required');

        $insights = new InsightModel();
        $alerts = $insights->getSmartAlerts($token);

        return $this->respond([
            'status' => self::STATUS_SUCCESS,
            'alerts' => $alerts
        ]);
    }

    public function financialRecurring(): ResponseInterface
    {
        $token = (string) $this->request->getPost('varUser');
        if (empty($token)) return $this->failUnauthorized('User token required');

        $insights = new InsightModel();
        $payments = $insights->getRecurringPayments($token);

        return $this->respond([
            'status' => self::STATUS_SUCCESS,
            'payments' => $payments
        ]);
    }

    public function financialTrends(): ResponseInterface
    {
        $token = (string) $this->request->getPost('varUser');
        if (empty($token)) return $this->failUnauthorized('User token required');

        $insights = new InsightModel();
        $trends = $insights->getSpendingTrends($token);

        return $this->respond([
            'status' => self::STATUS_SUCCESS,
            'trends' => $trends
        ]);
    }

    public function financialInsights(): ResponseInterface
    {
        $token = (string) $this->request->getPost('varUser');
        if (empty($token)) return $this->failUnauthorized('User token required');

        $insights = new InsightModel();
        $obs = $insights->getAIObservations($token);

        return $this->respond([
            'status' => self::STATUS_SUCCESS,
            'insights' => $obs
        ]);
    }
}
