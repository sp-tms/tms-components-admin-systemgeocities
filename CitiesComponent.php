<?php

namespace Apps\Tms\Components\System\Geo\Cities;

use Apps\Core\Packages\Adminltetags\Traits\DynamicTable;
use System\Base\BaseComponent;

class CitiesComponent extends BaseComponent
{
    use DynamicTable;

    protected $geoCities;

    public function initialize()
    {
        $this->geoCities = $this->basepackages->geoCities->init();
    }

    /**
     * @acl(name=view)
     */
    public function viewAction()
    {
        $countriesArr = $this->basepackages->geoCountries->geoCountries;
        $statesArr = $this->basepackages->geoStates->geoStates;

        if (isset($this->getData()['id'])) {
            if ($this->getData()['id'] != 0) {
                $city = $this->basepackages->geoCities->getById($this->getData()['id']);

                $this->view->city = $city;
            }

            if (!$this->view->city) {
                return $this->throwIdNotFound();
            }

            $this->view->pick('cities/view');

            $this->view->countries = [$countriesArr[$city['country_id']]];
            $this->view->states = [$statesArr[$city['state_id']]];

            return;
        }

        $controlActions =
            [
                // 'includeQ'              => true,
                'actionsToEnable'       =>
                [
                    'edit'      => 'system/geo/cities',
                ]
            ];

        if ($this->request->isPost()) {
            $countries = [];
            $states = [];

            if ($countriesArr) {
                foreach ($countriesArr as $countriesKey => $country) {
                    $countries[$country['id']] = $country['name'] . ' (' . $country['id'] . ')';
                }
            }

            if ($statesArr) {
                foreach ($statesArr as $statesKey => $state) {
                    $states[$state['id']] = $state['name'] . ' (' . $state['id'] . ')';
                }
            }

            $replaceColumns =
                [
                    'country_id'  =>
                        [
                            'html' => $countries
                        ],
                    'state_id'  =>
                        [
                            'html' => $states
                        ]
                ];
        } else {
            $replaceColumns = [];
        }

        $this->generateDTContent(
            $this->geoCities,
            'system/geo/cities/view',
            null,
            ['name', 'longitude', 'latitude', 'state_id', 'country_id'],
            true,
            ['name', 'longitude', 'latitude', 'state_id', 'country_id'],
            $controlActions,
            ['state_id'=>'State','country_id'=>'country'],
            $replaceColumns,
            'name',
            // $dtAdditionControlButtons
        );

        $this->view->pick('cities/list');
    }

    /**
     * @acl(name=add)
     */
    public function addAction()
    {
        $this->requestIsPost();

        $this->geoCities->addCity($this->postData());

        $this->addResponse(
            $this->geoCities->packagesData->responseMessage,
            $this->geoCities->packagesData->responseCode
        );
    }

    /**
     * @acl(name=update)
     */
    public function updateAction()
    {
        $this->requestIsPost();

        $this->geoCities->updateCity($this->postData());

        $this->addResponse(
            $this->geoCities->packagesData->responseMessage,
            $this->geoCities->packagesData->responseCode
        );
    }

    public function searchCityAction()
    {
        $this->requestIsPost();

        if ($this->postData()['search']) {
            $searchQuery = $this->postData()['search'];

            if (strlen($searchQuery) < 3) {
                return;
            }

            $this->basepackages->geoCities->searchCities($searchQuery);

            $this->addResponse(
                $this->basepackages->geoCities->packagesData->responseMessage,
                $this->basepackages->geoCities->packagesData->responseCode,
                $this->basepackages->geoCities->packagesData->responseData ?? []
            );
        } else {
            $this->addResponse('Search Query Missing', 1);
        }
    }
}
