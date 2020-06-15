<?php

namespace App\Admin;

use App\Entity\Product;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Class ProductAdmin
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductAdmin extends AbstractAdmin
{
    /**
     *
     * @param string $action
     * @param null $object
     *
     * @return array
     */
    public function configureActionButtons($action, $object = null)
    {
        $list = parent::configureActionButtons($action, $object);

        $list['import']['template'] = 'ProductAdmin/import_button.html.twig';

        return $list;
    }

    /**
     *
     * @return array
     */
    public function getDashboardActions()
    {
        $actions = parent::getDashboardActions();

        $actions['import']['template'] = 'ProductAdmin/import_dashboard_button.html.twig';

        return $actions;
    }

    /**
     * @return array
     */
    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'Room Code' => 'identifier',
            'Room Name' => 'name',
            'Partner' => 'partner.name',
            'Chained' => 'chained',
            'Master Room' => 'masterProduct.name',
            'Child Rooms' => 'linkedProductsName',
            'Created At' => 'createdAtFormatted',
            'Updated At' => 'updatedAtFormatted',
            'Description' => 'description',
            'Sellable' => 'sellableAsString',
            'Reservable' => 'reservableAsString',
        ];
    }

    /**
     *
     * @param RouteCollection $collection
     *
     * @return void
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->add('import')
            ->add('listAvailability')
            ->add('listPrice');
    }

    /**
     *
     * @param DatagridMapper $datagridMapper
     *
     * @return void
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('identifier', null, ['label' => 'Room Code'])
            ->add('name', null, ['label' => 'Room Name'])
            ->add('partner.identifier', null, ['label' => 'Partner URN', 'show_filter' => true])
            ->add('partner.channelManager');
    }

    /**
     *
     * @param ListMapper $listMapper
     *
     * @return void
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('identifier', null, ['label' => 'Room Code'])
            ->add('name', null, ['label' => 'Room Name'])
            ->add('partner')
            ->add(
                'chained',
                null,
                [
                    'label' => 'Chained',
                    'template' => 'ProductAdmin/list__chained_field.html.twig',
                ]
            )
            ->add('sellable')
            ->add('reservable')
            ->add('masterProduct', null, ['label' => 'Master Room'])
            ->add('linkedProducts', null, ['label' => 'Child Rooms'])
            ->add('createdAt')
            ->add('updatedAt')
            ->add(
                '_action',
                null,
                [
                    'actions' => [
                        'show' => [],
                        'edit' => [],
                        'delete' => [],
                        'listAvailability' => [
                            'template' => 'ProductAdmin/link_availability_button.html.twig',
                        ],
                        'listPrice' => [
                            'template' => 'ProductAdmin/link_price_button.html.twig',
                        ],
                    ],
                ]
            );
    }

    /**
     *
     * @param FormMapper $formMapper
     *
     * @return void
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Product $product */
        $product = $this->getSubject();
        $disabled = ($product->getId()) ? true : false;

        $formMapper
            ->add(
                'identifier',
                null,
                [
                    'disabled' => $disabled,
                    'label' => 'Room Code',
                ]
            )
            ->add('name', null, ['label' => 'Room Name', 'empty_data' => ''])
            ->add('sellable')
            ->add('reservable');

        if ($product->isMaster()) {
            $childIds = array_map(function (Product $item) {
                return $item->getId();
            }, $product->getLinkedProducts()->toArray());
            $formMapper
                ->add(
                    'linkedProducts',
                    null,
                    [
                    'label' => 'Add/Remove Child Rooms',
                    'by_reference' => false,
                    'query_builder' => function (EntityRepository $er) use ($product, $childIds) {
                        $subQb = $er->createQueryBuilder('p');
                        $subQb
                            ->select('IDENTITY(p.masterProduct)')
                            ->where('p.partner = :partner')
                            ->andWhere('p.masterProduct IS NOT NULL');

                        $qb = $er->createQueryBuilder('product');

                        $qb->where('product.partner = :partner')
                            ->andWhere('product.identifier != :identifier')
                            ->andWhere(
                                $qb->expr()->notIn(
                                    'product.id',
                                    $subQb->getDQL()
                                )
                            );

                        if (!empty($childIds)) {
                            $qb->andWhere($qb->expr()->orX(
                                'product.masterProduct IS NULL',
                                $qb->expr()->in('product.id', $childIds)
                            ));
                        }

                        if (empty($childIds)) {
                            $qb->andWhere('product.masterProduct IS NULL');
                        }

                        return $qb->setParameter('partner', $product->getPartner())
                            ->setParameter('identifier', $product->getIdentifier());
                    },
                    ],
                    [
                    'allow_delete' => true,
                    'delete_empty' => function (Product $product = null) {
                        return null === $product;
                    },
                    ]
                );
        }

        if ($disabled && $product->getMasterProduct()) {
            $formMapper->add(
                'linkedProducts',
                null,
                [
                    'label' => 'Add/Remove Child Rooms',
                    'by_reference' => false,
                    'help' => 'This room is currently a child room in a chain',
                    'disabled' => true,
                ]
            );
        }

        if (!$disabled) {
            $formMapper->add('description');
            $formMapper->add('partner', ModelAutocompleteType::class, ['property' => 'identifier']);
        }
    }

    /**
     *
     * @param ShowMapper $showMapper
     *
     * @return void
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('identifier', null, ['label' => 'Room Code'])
            ->add('name', null, ['label' => 'Room Name'])
            ->add('sellable')
            ->add('reservable')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('linkedProducts', null, ['label' => 'Child Rooms'])
            ->add('masterProduct', null, ['label' => 'Master Room']);
    }
}
