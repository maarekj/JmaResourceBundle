<?php
/**
 * Created by PhpStorm.
 * User: maarek
 * Date: 09/04/2014
 * Time: 21:11
 */

namespace Jma\ResourceBundle\Tests\Repository;

use Doctrine\ORM\Query\Expr;
use Jma\ResourceBundle\Repository\EntityRepository;

class EntityRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityRepository
     */
    protected $repo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $qb;

    public function setUp()
    {
        $this->qb = $this
            ->getMockBuilder("Doctrine\ORM\QueryBuilder")
            ->disableOriginalConstructor()
            ->getMock();
        $this->qb->expects($this->any())->method("select")->will($this->returnSelf());
        $this->qb->expects($this->any())->method("from")->will($this->returnSelf());

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $em->expects($this->any())
            ->method("createQueryBuilder")
            ->willReturn($this->qb);

        $class = $this
            ->getMockBuilder("Doctrine\ORM\Mapping\ClassMetadata")
            ->disableOriginalConstructor()
            ->getMock();

        $this->repo = new EntityRepository($em, $class);
    }

    public function providerInnerLeftFetchAlias()
    {
        return [
            ["inner", true, null],
            ["left", true, null],
            ["inner", false, null],
            ["left", false, null],
            ["inner", true, ["a", "b"]],
            ["left", true, ["a", "b"]],
            ["inner", false, ["a", "b"]],
            ["left", false, ["a", "b"]],
        ];
    }

    /**
     * @dataProvider providerInnerLeftFetchAlias
     */
    public function testJoin($type, $fetch, $alias)
    {
        if ($alias === null) {
            $alias = ['relation'];
        }

        $this->qb
            ->expects($this->exactly(1))
            ->method($type . "Join")
            ->with("o.relation", $alias[0])
            ->will($this->returnSelf());

        if ($fetch) {
            $this->qb
                ->expects($this->exactly(1))
                ->method("addSelect")
                ->with($alias[0])
                ->will($this->returnSelf());
        } else {
            $this->qb->expects($this->never())->method("addSelect");
        }

        $this->repo->builderAll(['_join' => [
            ["field" => 'relation', "type" => $type, "fetch" => $fetch, "alias" => $alias[0]]
        ]]);
    }

    /**
     * @dataProvider providerInnerLeftFetchAlias
     */
    public function testMultiJoin($type, $fetch, $alias)
    {
        if ($alias === null) {
            $alias = ['relation1', 'relation2'];
        }

        try {
            $this->qb
                ->expects($this->exactly(2))
                ->method($type . "Join")
                ->withConsecutive(
                    ['o.relation1', $alias[0]],
                    ['o.relation2', $alias[1]]
                )
                ->will($this->returnSelf());
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }

        if ($fetch) {
            $this->qb
                ->expects($this->exactly(2))
                ->method("addSelect")
                ->withConsecutive(
                    [$alias[0]],
                    [$alias[1]]
                )
                ->will($this->returnSelf());
        } else {
            $this->qb->expects($this->never())->method("addSelect");
        }

        $this->repo->builderAll([
            '_join' => [
                ["field" => 'relation1', "type" => $type, "fetch" => $fetch, "alias" => $alias[0]],
                ["field" => 'relation2', "type" => $type, "fetch" => $fetch, "alias" => $alias[1]],
            ]
        ]);
    }

    public function testOneInnerJoinAndOneLeftJoin()
    {
        $this->qb
            ->expects($this->exactly(1))
            ->method("leftJoin")
            ->withConsecutive(
                ['o.relation1', 'relation1']
            )
            ->will($this->returnSelf());

        $this->qb
            ->expects($this->exactly(1))
            ->method("innerJoin")
            ->withConsecutive(
                ['o.relation2', 'relation2']
            )
            ->will($this->returnSelf());


        $this->qb
            ->expects($this->exactly(1))
            ->method("addSelect")
            ->with('relation1')
            ->will($this->returnSelf());

        $this->repo->builderAll([
            '_join' => [
                ["field" => 'relation1', "type" => "left", "fetch" => true],
                ["field" => 'relation2', "type" => "inner", "fetch" => false],
            ]
        ]);
    }

    public function testMultiCriteria()
    {
        $expr = new Expr();
        $this->qb->expects($this->any())->method("expr")->willReturn($expr);

        // On teste la jointure et le fetch
        //
        // join
        $this->qb->expects($this->exactly(1))->method("leftJoin")->withConsecutive(
            ['o.relation', 'relation']
        )->will($this->returnSelf());
        //
        // fetch
        //
        $this->qb->expects($this->exactly(1))->method("addSelect")
            ->with('relation')->will($this->returnSelf());

        // On teste les differents critère:
        // - critère null
        // - critère simple
        // - critère array
        //
        $this->qb->expects($this->exactly(3))->method('andWhere')
            ->withConsecutive(
                ['o.field1 IS NULL'],
                [$this->isExprEqual($expr->eq('o.field2', ':field2'))],
                [$this->isExprEqual($expr->in('o.field3', ['1', '2']))]
            )
            ->will($this->returnSelf());

        $this->qb->expects($this->exactly(1))->method("setParameter")
            ->with('field2', 'value2')->will($this->returnSelf());

        $this->repo->builderAll([
            '_join' => [
                'relation'
            ],
            'field1' => null,
            'field2' => 'value2',
            'field3' => ['1', '2']
        ]);
    }

    public function isExprEqual($expected)
    {
        return $this->callback(function ($e) use ($expected) {
            return $e->__toString() === $expected->__toString();
        });
    }
}
 